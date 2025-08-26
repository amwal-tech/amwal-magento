// Global flag to track initialization state
let amwalSentryInitialized = false;
let amwalSentryInitializing = false;

export interface AmwalSentryConfig {
    dsn: string;
    environment?: string;
    tracesSampleRate?: number;
    replaysSessionSampleRate?: number;
    replaysOnErrorSampleRate?: number;
    release?: string;
    beforeSend?: (event: any) => any | null;
    enablePerformanceMonitoring?: boolean;
    enableSessionReplay?: boolean;
    debug?: boolean;
}

const importSentry = async (): Promise<{ Sentry: any } | null> => {
    try {
        // Try dynamic import first (modern bundlers)
        const sentryReact = await import('@sentry/react');
        return { Sentry: sentryReact };
    } catch {
        try {
            // Fallback to global window object if Sentry was loaded via script tag
            if (typeof window !== 'undefined' && (window as any).Sentry) {
                return { Sentry: (window as any).Sentry };
            }
        } catch {
            // Sentry not available
        }
        return null;
    }
};

/**
 * Check if an error is Amwal-related
 */
const isAmwalRelatedError = (event: any): boolean => {
    // Check error message for Amwal-related keywords
    const errorMessage = event.exception?.values?.[0]?.value || event.message || '';
    const amwalKeywords = [
        'amwal',
        'Amwal',
        'AMWAL',
        'amwal-checkout-button',
        'AmwalCheckoutButton',
        'amwal-payment',
        'amwal_payments'
    ];

    if (amwalKeywords.some(keyword => errorMessage.includes(keyword))) {
        return true;
    }

    // Check stack trace for Amwal-related files/functions
    const stackFrames = event.exception?.values?.[0]?.stacktrace?.frames || [];
    for (const frame of stackFrames) {
        const filename = frame.filename || '';
        const functionName = frame.function || '';

        if (amwalKeywords.some(keyword =>
            filename.toLowerCase().includes(keyword.toLowerCase()) ||
            functionName.toLowerCase().includes(keyword.toLowerCase())
        )) {
            return true;
        }
    }

    // Check breadcrumbs for Amwal context
    const breadcrumbs = event.breadcrumbs || [];
    for (const breadcrumb of breadcrumbs) {
        if (breadcrumb.category === 'amwal.payment' ||
            breadcrumb.message?.includes('Amwal') ||
            breadcrumb.data?.amwal_context) {
            return true;
        }
    }

    // Check tags for Amwal-related context
    const tags = event.tags || {};
    if (tags.component === 'amwal-payment' ||
        tags.amwal_context ||
        tags.transaction_id) {
        return true;
    }

    // Check contexts for Amwal-related data
    const contexts = event.contexts || {};
    if (contexts.amwal_payment || contexts.amwal_error_data) {
        return true;
    }

    // Check request URLs for Amwal endpoints
    const requestUrl = event.request?.url || '';
    if (requestUrl.includes('/amwal/') || requestUrl.includes('amwal')) {
        return true;
    }

    return false;
};

/**
 * Initialize Sentry with Amwal-optimized configuration that only reports Amwal JS errors
 * Call this in your main app's index.tsx or App.tsx
 */
export const initAmwalSentry = async (config: AmwalSentryConfig): Promise<void> => {
    // Enhanced guard clause to prevent multiple initializations
    if (amwalSentryInitialized || amwalSentryInitializing) {
        console.log('[Amwal] Sentry already initialized or initializing, skipping...');
        return;
    }

    // Additional check for existing Sentry initialization
    if (isAmwalSentryEnabled()) {
        amwalSentryInitialized = true;
        return;
    }

    amwalSentryInitializing = true;

    try {
        // Import Sentry dynamically
        const sentryModules = await importSentry();
        if (!sentryModules) {
            console.warn('[Amwal] Sentry not available. Install @sentry/react to enable error tracking.');
            return;
        }

        const { Sentry } = sentryModules;

        const {
            dsn,
            environment = 'development',
            tracesSampleRate = environment === 'production' ? 0.1 : 1.0,
            replaysSessionSampleRate = environment === 'production' ? 0.1 : 0,
            replaysOnErrorSampleRate = environment === 'production' ? 1.0 : 0,
            release = '1.0.0',
            enablePerformanceMonitoring = true,
            enableSessionReplay = true,
            debug = false,
            beforeSend
        } = config;

        // Build integrations array
        const integrations: any[] = [];

        // Add BrowserTracing if available and enabled (compatible with Sentry v7 and v8+)
        if (enablePerformanceMonitoring) {
            try {
                if (typeof Sentry.browserTracingIntegration === 'function') {
                    // Sentry v8+ style
                    integrations.push(Sentry.browserTracingIntegration());
                } else if (Sentry.BrowserTracing) {
                    // Sentry v7 style
                    integrations.push(new Sentry.BrowserTracing());
                } else {
                    console.warn('[Amwal] BrowserTracing integration is not available in this Sentry version.');
                }
            } catch (error) {
                console.warn('[Amwal] Failed to initialize BrowserTracing:', error);
            }
        }

        // Add Replay if available and enabled (compatible with Sentry v7 and v8+)
        if (enableSessionReplay) {
            try {
                const replayOptions = {
                    maskAllText: true,
                    maskAllInputs: true,
                    sessionSampleRate: replaysSessionSampleRate,
                    errorSampleRate: replaysOnErrorSampleRate,
                };

                if (typeof Sentry.replayIntegration === 'function') {
                    // Sentry v8+ style
                    integrations.push(Sentry.replayIntegration(replayOptions));
                } else {
                    // Sentry v7 style - requires a separate, safe import
                    let ReplayIntegration = Sentry.Replay; // First check if it's on the main object
                    if (!ReplayIntegration) {
                        try {
                            // If not, try to dynamically import it
                            const replayModule = await import('@sentry/replay');
                            ReplayIntegration = replayModule.Replay;
                        } catch (e) {
                            // This will fail if @sentry/replay is not installed.
                            ReplayIntegration = null;
                        }
                    }

                    if (ReplayIntegration) {
                        integrations.push(new ReplayIntegration(replayOptions));
                    } else {
                        console.warn('[Amwal] Replay integration is not available. Try installing @sentry/replay.');
                    }
                }
            } catch (error) {
                console.warn('[Amwal] Failed to initialize Replay:', error);
            }
        }

        // Initialize Sentry with Amwal-optimized settings
        Sentry.init({
            dsn,
            environment,
            tracesSampleRate,
            release,
            debug,
            integrations,

            // Enhanced beforeSend for Amwal payment security AND filtering only Amwal errors
            beforeSend: (event: any) => {
                // FIRST: Check if this is an Amwal-related error
                // If not, drop the event entirely
                if (!isAmwalRelatedError(event)) {
                    if (debug) {
                        console.log('[Amwal] Dropping non-Amwal error:', event);
                    }
                    return null;
                }

                // Apply user's custom beforeSend first (only for Amwal errors)
                if (beforeSend) {
                    event = beforeSend(event);
                    if (!event) return null;
                }

                // Remove sensitive Amwal payment data
                if (event.contexts?.amwal_payment) {
                    delete event.contexts.amwal_payment.cartId;
                    delete event.contexts.amwal_payment.refId;
                }

                // Filter sensitive request data
                if (event.request?.url?.includes('/amwal/')) {
                    delete event.request.data;
                }

                // Remove sensitive breadcrumbs
                if (event.breadcrumbs) {
                    event.breadcrumbs = event.breadcrumbs.map((breadcrumb: any) => {
                        if (breadcrumb.data?.cartId) delete breadcrumb.data.cartId;
                        if (breadcrumb.data?.refId) delete breadcrumb.data.refId;
                        return breadcrumb;
                    });
                }

                // Add additional Amwal-specific tags for better categorization
                event.tags = {
                    ...event.tags,
                    amwal_filtered: true,
                    component: 'amwal-payment'
                };

                return event;
            },

            // Initial scope for Amwal
            initialScope: {
                tags: {
                    component: 'amwal-payment',
                    integration: 'magento',
                    plugin_version: '1.0.0',
                    amwal_filtered: true
                }
            }
        });

        // Set global flags that Sentry is initialized for Amwal
        if (typeof window !== 'undefined') {
            (window as any).AmwalSentryEnabled = true;
        }
        amwalSentryInitialized = true;

        console.log('[Amwal] Sentry initialized successfully (Amwal errors only)');

    } catch (error) {
        console.warn('[Amwal] Failed to initialize Sentry:', error);
    } finally {
        amwalSentryInitializing = false;
    }
};

/**
 * Check if Sentry is available and initialized
 */
export const isAmwalSentryEnabled = (): boolean => {
    try {
        return typeof window !== 'undefined' && !!(window as any).AmwalSentryEnabled;
    } catch {
        return false;
    }
};

/**
 * Report an Amwal-specific error manually
 */
export const reportAmwalError = async (
    error: Error | string,
    context: string,
    additionalData?: Record<string, any>
): Promise<void> => {
    try {
        const sentryModules = await importSentry();
        if (!sentryModules || !isAmwalSentryEnabled()) {
            console.error(`[Amwal ${context}]:`, error, additionalData);
            return;
        }

        const { Sentry } = sentryModules;

        Sentry.withScope((scope: any) => {
            scope.setTag('amwal_context', context);
            scope.setTag('amwal_manual_report', true);

            // Set transaction_id as a tag if it exists in additionalData
            if (additionalData?.transaction_id) {
                scope.setTag('transaction_id', additionalData.transaction_id);
            }

            scope.setContext('amwal_error_data', additionalData || {});
            scope.setLevel('error');

            if (error instanceof Error) {
                Sentry.captureException(error);
            } else {
                Sentry.captureMessage(`Amwal ${context}: ${error}`, 'error');
            }
        });
    } catch {
        // Sentry not available, just log to console
        console.error(`[Amwal ${context}]:`, error, additionalData);
    }
};

/**
 * Add a breadcrumb for Amwal payment flow tracking
 */
export const addAmwalBreadcrumb = async (
    message: string,
    data?: Record<string, any>,
    level: 'info' | 'warning' | 'error' = 'info'
): Promise<void> => {
    try {
        const sentryModules = await importSentry();
        if (!sentryModules || !isAmwalSentryEnabled()) return;

        const { Sentry } = sentryModules;

        Sentry.addBreadcrumb({
            message: `Amwal: ${message}`,
            level,
            data: data || {},
            category: 'amwal.payment'
        });
    } catch {
        // Sentry not available, ignore
    }
};

/**
 * Set user context for better error tracking
 */
export const setAmwalUserContext = async (user: {
    id?: string;
    email?: string;
    username?: string;
}): Promise<void> => {
    try {
        const sentryModules = await importSentry();
        if (!sentryModules || !isAmwalSentryEnabled()) return;

        const { Sentry } = sentryModules;

        Sentry.setUser(user);
    } catch {
        // Sentry not available, ignore
    }
};

/**
 * Start a performance transaction
 */
export const startAmwalTransaction = async (
    name: string,
    operation: string,
    additionalTags?: Record<string, any>
): Promise<any> => {
    try {
        const sentryModules = await importSentry();
        if (!sentryModules || !isAmwalSentryEnabled()) {
            return { setStatus: () => {}, finish: () => {} };
        }

        const { Sentry } = sentryModules;

        return Sentry.startTransaction({
            name: `Amwal: ${name}`,
            op: `amwal.${operation}`,
            tags: {
                component: 'amwal-payment',
                amwal_transaction: true,
                ...additionalTags
            }
        });
    } catch {
        return { setStatus: () => {}, finish: () => {} };
    }
};

/**
 * Pre-configured Sentry configs for common environments
 */
export const AmwalSentryConfigs = {
    development: (dsn: string): AmwalSentryConfig => ({
        dsn,
        environment: 'development',
        tracesSampleRate: 1.0,
        replaysSessionSampleRate: 0,
        replaysOnErrorSampleRate: 0,
        debug: true,
        enablePerformanceMonitoring: true,
        enableSessionReplay: false
    }),

    staging: (dsn: string): AmwalSentryConfig => ({
        dsn,
        environment: 'staging',
        tracesSampleRate: 0.5,
        replaysSessionSampleRate: 0.1,
        replaysOnErrorSampleRate: 1.0,
        debug: false,
        enablePerformanceMonitoring: true,
        enableSessionReplay: true
    }),

    production: (dsn: string): AmwalSentryConfig => ({
        dsn,
        environment: 'production',
        tracesSampleRate: 0.1,
        replaysSessionSampleRate: 0.1,
        replaysOnErrorSampleRate: 1.0,
        debug: false,
        enablePerformanceMonitoring: true,
        enableSessionReplay: true
    })
};
