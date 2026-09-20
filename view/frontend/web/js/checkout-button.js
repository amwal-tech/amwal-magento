define([
    'jquery',
    'uiComponent',
    'placeAmwalOrder',
    'payAmwalOrder',
    'amwalErrorHandler',
    'mage/url',
    'Magento_Customer/js/customer-data',
    'underscore',
    'mage/translate',
    'Magento_Ui/js/modal/confirm',
    'mage/validation',
    'domReady!'
],
function ($, Component, placeAmwalOrder, payAmwalOrder, amwalErrorHandler, urlBuilder, customerData, _, $t, confirm) {
    'use strict';

    return Component.extend({
        buttonId: null,
        triggerContext: null,
        productButtonContainer: null,
        amwalCheckoutButton: null,
        hideProceedToCheckout: false,
        redirectOnLoadClick: false,

        /**
         * @returns {exports.initialize}
         */
        initialize: function () {
            this._super();
            let self = this;

            self.productButtonContainer = document.getElementById(self.buttonId);
            if (window.renderReactElement) {
                window.renderReactElement(self.productButtonContainer);

                if (self.triggerContext === 'product-detail-page' || self.triggerContext === 'product-listing-page') {
                    self.initializeProductDetail('product_addtocart_form');
                }
                if (self.triggerContext === 'amwal-widget' || self.triggerContext === 'product-list-widget') {
                    self.initializeProductDetail('form-' + self.buttonId);
                }
                if (self.triggerContext === 'minicart') {
                    self.initializeMiniCart();
                }
                if (self.triggerContext === 'login') {
                    self.initializeLogin();
                }
                if (self.redirectOnLoadClick && self.triggerContext === 'regular-checkout') {
                    const parentElement = document.getElementById(self.buttonId);
                    if (parentElement) {
                        const observer = new MutationObserver((mutationsList, observer) => {
                            const childElement = parentElement.querySelector('.amwal-checkout-button');
                            if (childElement) {
                                childElement.click();
                                observer.disconnect();
                            }
                        });
                        observer.observe(parentElement, { childList: true, subtree: true });
                    }
                }
                window.addEventListener('cartUpdateNeeded', function(e) {
                    var sections = ['cart'];
                    customerData.invalidate(sections);
                    customerData.reload(sections, true);
                });
            }

            return self;
        },

        /**
         * Initializes the Product Detail Page specific actions
         */
        initializeProductDetail: function (formID) {
            let self = this;
            let bypassingModal = false;
            const isProductDetailPage = self.triggerContext === 'product-detail-page';

            const amwalButtonObserver = new MutationObserver((mutations) => {
                const amwalCheckoutButton = self.productButtonContainer.querySelector('amwal-checkout-button');
                if (amwalCheckoutButton) {
                    amwalCheckoutButton.setAttribute('disabled', true);
                    addFormListeners();
                    if (isProductDetailPage) {
                        attachButtonClickListener(amwalCheckoutButton);
                    }
                    amwalButtonObserver.disconnect();
                }
                if (!isProductDetailPage) {
                    const cart = customerData.get('cart');
                    cart.subscribe(function (updatedCartData) {
                        if (updatedCartData.summary_count > 0) {
                            self.productButtonContainer.classList.add('hidden');
                        } else {
                            self.productButtonContainer.classList.remove('hidden');
                        }
                        addFormListeners();
                    }, this);
                }
            });
            amwalButtonObserver.observe(self.productButtonContainer, {
                childList: true,
                subtree: true,
                attributes: false,
                characterData: false
            });

            const addToCartForm = $("#" + formID);

            /**
             * Check if the product form is valid
             * @return Boolean
             */
            const isProductFormValid = () => {
                // Initialize validation if not already done
                if (!addToCartForm.data('validation')) {
                    addToCartForm.validation();
                }
                const formIsValid = addToCartForm.validation('isValid');
                addToCartForm.validation('clearError');
                return formIsValid;
            };

            /**
             * Check if the cart is empty
             * @return Boolean
             */
            const isCartEmpty = () => {
                const cart = customerData.get('cart');
                const cartData = cart();
                return !cartData || !cartData.summary_count || cartData.summary_count === 0;
            };

            /**
             * Toggle the button disabled attribute based on form status
             */
            const updateButtonStatus = () => {
                const amwalButton = $("#" + self.buttonId + " amwal-checkout-button");
                if (isProductDetailPage || isCartEmpty()) {
                    amwalButton.removeClass('hidden');
                } else {
                    amwalButton.addClass('hidden');
                }
                if (isProductFormValid()) {
                    amwalButton.removeAttr('disabled');
                } else {
                    amwalButton.attr('disabled', true);
                }
            };

            /**
             * Listen to form changes to update button status.
             */
            const addFormListeners = () => {
                addToCartForm.ready(function () {
                    updateButtonStatus();
                });

                addToCartForm.on('change', function () {
                    updateButtonStatus();
                });
            };

            /**
             * Clean quote in backend
             */
            const cleanQuote = () => {
                const scopeCode = self.productButtonContainer.getAttribute('data-scope-code');
                const cleanUrl = urlBuilder.build((scopeCode ? 'rest/' + scopeCode + '/' : 'rest/') + 'V1/amwal/clean-quote');
                return $.ajax({
                    url: cleanUrl,
                    type: 'POST',
                    contentType: 'application/json',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
            };

            /**
             * Show cart conflict modal
             */
            const showCartConflictModal = (amwalCheckoutButton) => {
                const cartData = customerData.get('cart')();
                const items = (cartData && cartData.items) ? cartData.items : [];
                const summaryCount = (cartData && cartData.summary_count) ? cartData.summary_count : items.length;
                const subtotal = (cartData && cartData.subtotal) ? cartData.subtotal : '';

                let itemsHtml = '';
                if (items.length > 0) {
                    const itemsListHtml = items.map(function (item) {
                        let optionsText = '';
                        if (item.options && item.options.length) {
                            optionsText = item.options.map(function (opt) {
                                return opt.value;
                            }).join(' · ');
                        }

                        const imgHtml = (item.product_image && item.product_image.src)
                            ? '<img class="amwal-cart-item-img" src="' + item.product_image.src + '" alt="' + (item.product_name || '') + '" />'
                            : '<div class="amwal-cart-item-placeholder">' +
                                '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2">' +
                                    '<path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>' +
                                    '<line x1="3" y1="6" x2="21" y2="6"></line>' +
                                '</svg>' +
                              '</div>';

                        return '<div class="amwal-cart-item-row">' +
                            imgHtml +
                            '<div class="amwal-cart-item-details">' +
                                '<div class="amwal-cart-item-name" title="' + (item.product_name || '') + '">' + (item.product_name || '') + '</div>' +
                                '<div class="amwal-cart-item-meta">' +
                                    (optionsText ? '<span class="amwal-cart-item-options" title="' + optionsText + '">' + optionsText + '</span><span class="amwal-cart-item-bullet">·</span>' : '') +
                                    '<span class="amwal-cart-item-qty">' + $t('Qty') + ': ' + item.qty + '</span>' +
                                '</div>' +
                            '</div>' +
                            '<div class="amwal-cart-item-price">' + (item.product_price || '') + '</div>' +
                        '</div>';
                    }).join('');

                    const scrollHintHtml = items.length > 3
                        ? '<div class="amwal-cart-scroll-hint">' +
                            '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">' +
                                '<path d="M6 9l6 6 6-6"/>' +
                            '</svg>' +
                            '<span>' + $t('Scroll to view all %1 items').replace('%1', summaryCount) + '</span>' +
                          '</div>'
                        : '';

                    itemsHtml = '<div class="amwal-cart-items-container">' +
                        '<div class="amwal-cart-items-header">' +
                            '<span class="amwal-cart-items-count">' +
                                '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                                    '<circle cx="9" cy="21" r="1"></circle>' +
                                    '<circle cx="20" cy="21" r="1"></circle>' +
                                    '<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>' +
                                '</svg>' +
                                '<span>' + $t('Items in cart') + ' (' + summaryCount + ')</span>' +
                            '</span>' +
                            (subtotal ? '<span class="amwal-cart-items-subtotal">' + subtotal + '</span>' : '') +
                        '</div>' +
                        '<div class="amwal-cart-items-list">' +
                            itemsListHtml +
                        '</div>' +
                        scrollHintHtml +
                    '</div>';
                }

                const amwalLogoSvg = '<svg class="amwal-logo-svg" viewBox="0 15 1024 300" height="26" width="90" fill="#111827" xmlns="http://www.w3.org/2000/svg">' +
                    '<g>' +
                        '<path d="M260.835 108.9C289.335 87.6 338.435 94.8 360.035 122C361.035 123.2 362.835 123.3 363.835 122.2C364.935 121 365.935 119.8 367.035 118.7C378.535 107.1 392.735 100.8 408.635 98C425.435 95 442.035 95.9 458.035 102.2C477.435 109.8 490.535 124 498.635 142.9C504.535 156.5 507.335 170.9 507.435 185.6C507.635 216 507.535 246.5 507.735 276.9C507.735 279.4 507.035 279.8 504.735 279.8C489.935 279.7 475.135 279.7 460.435 279.8C458.035 279.8 457.335 279.3 457.335 276.8C457.435 248 457.435 219.2 457.435 190.4C457.435 182 456.035 173.9 452.035 166.4C439.335 142.6 407.635 140.2 391.135 161.9C384.835 170.2 381.835 179.7 381.835 190C381.835 219 381.835 248 381.935 277C381.935 279.3 381.335 279.8 379.135 279.8C364.035 279.7 348.835 279.7 333.735 279.8C331.735 279.8 331.235 279.3 331.235 277.3C331.335 248.3 331.235 219.3 331.335 190.3C331.335 184.9 330.835 179.5 329.535 174.2C325.835 160 313.835 149.3 299.335 147.3C277.035 144.2 263.635 161 259.235 175.7C257.635 180.9 257.035 186.2 257.035 191.7C257.035 219.3 257.035 247 257.135 274.6C257.135 277.5 254.835 279.8 251.935 279.8C237.635 279.7 223.335 279.7 208.935 279.8C206.835 279.8 206.435 279.2 206.435 277.3C206.535 219.1 206.535 161 206.435 102.8C206.435 101 206.735 100.4 208.735 100.4C224.135 100.5 239.535 100.5 254.935 100.4C256.535 100.4 257.135 100.7 257.035 102.4C256.935 104 256.935 105.5 256.935 107.1C256.935 109 259.235 110.1 260.835 108.9Z"/>' +
                        '<path d="M679.735 279.9C678.835 281.7 676.235 281.7 675.335 279.9C669.235 267.2 663.235 254.7 657.235 242.2C651.235 229.7 645.235 217.3 639.235 204.6C638.335 202.7 635.735 202.7 634.835 204.6C622.735 229.8 610.735 254.7 598.635 280.1C597.735 281.9 595.235 282 594.235 280.2C562.435 220.1 530.835 160.4 498.935 100.3C500.135 100.3 500.935 100.3 501.735 100.3C518.635 100.3 535.635 100.3 552.535 100.2C554.435 100.2 555.335 100.9 556.135 102.5C569.535 129.6 583.035 156.6 596.535 183.7C596.935 184.4 597.235 185.1 597.835 186.3C600.335 181.1 602.635 176.2 604.835 171.4C607.635 165.6 610.335 159.7 613.135 153.9C613.835 152.5 613.835 151.4 613.135 149.9C605.735 134.3 598.335 118.6 591.035 102.9C590.735 102.2 590.335 101.5 590.035 100.7C590.535 100 591.235 100.3 591.835 100.3C606.635 100.3 621.435 100.3 636.235 100.2C637.935 100.2 638.735 100.8 639.435 102.3C652.535 129.8 665.735 157.3 678.935 184.8C679.035 185 679.235 185.2 679.635 185.7C681.735 180.9 683.835 176.4 685.835 171.8C695.935 148.7 706.135 125.6 716.135 102.5C716.935 100.7 717.835 100.2 719.735 100.2C734.535 100.3 749.335 100.2 764.135 100.2C764.935 100.2 765.735 100.2 766.835 100.2C737.735 160.3 708.835 219.9 679.735 279.9Z"/>' +
                        '<path d="M1015.64 147.3C1015.64 190.4 1015.64 233.5 1015.74 276.6C1015.74 279 1015.34 279.7 1012.74 279.7C997.635 279.6 982.435 279.6 967.335 279.7C965.435 279.7 965.035 279.2 965.035 277.3C965.135 190.7 965.135 104.1 965.035 17.6C965.035 15.4 965.735 15 967.735 15C982.735 15.1 997.835 15.1 1012.84 15C1015.24 15 1015.74 15.6 1015.74 18C1015.54 61.1 1015.64 104.2 1015.64 147.3Z"/>' +
                        '<path d="M940.435 103.5C940.435 103.1 940.335 102.6 940.435 102.2C940.635 100.8 940.135 100.4 938.635 100.4C923.835 100.4 908.935 100.4 894.135 100.4C891.735 100.4 889.835 102.3 889.835 104.7C889.835 105.6 889.835 106.5 889.835 107.4C889.835 109.4 887.535 110.5 885.935 109.4C875.235 101.6 863.235 97.5 850.235 96.8C823.635 95.3 801.135 105.3 783.335 124.8C761.135 149.2 753.735 178.1 759.935 210.3C767.235 247.7 797.435 279.4 837.735 283.4C852.735 284.9 867.135 282.5 880.435 275C882.335 273.9 884.035 272.7 885.735 271.3C887.335 270 889.735 271.1 889.735 273.2C889.735 274.7 889.735 276.1 889.635 277.6C889.535 279.5 890.035 280 891.935 280C906.135 279.9 920.335 279.9 934.635 280C937.735 280 940.335 277.5 940.335 274.4C940.335 217.2 940.435 160.3 940.435 103.5ZM888.435 208.6C882.135 224.1 867.935 233.5 851.035 233.4C824.335 233 806.635 211 809.535 185.3C811.635 166.1 823.335 152.7 840.635 148.2C865.035 141.9 888.635 158.1 891.435 183.2C892.435 192 891.735 200.5 888.435 208.6Z"/>' +
                        '<path d="M182.835 103.5C182.835 103 182.735 102.6 182.835 102.2C183.035 100.8 182.535 100.4 181.035 100.4C165.235 100.4 149.535 100.5 133.735 100.4C132.135 100.4 132.035 101.1 132.035 102.3C132.035 104 132.035 105.6 132.035 107.4C132.035 109.4 129.835 110.5 128.135 109.4C117.435 101.7 105.435 97.6 92.4351 96.9C66.9351 95.4 45.0351 104.5 27.6351 122.8C1.53511 149.9-5.86489 182.7 4.53511 218.8C15.7351 257.7 50.8351 284.8 90.4351 283.5C104.235 283 117.035 279.8 128.035 271.1C129.635 269.8 132.035 270.9 132.035 273C132.035 274.4 132.035 275.8 131.935 277.2C131.835 279.2 132.335 279.7 134.435 279.7C149.535 279.6 164.535 279.6 179.635 279.7C182.135 279.7 182.935 279.2 182.935 276.6C182.735 218.9 182.835 161.2 182.835 103.5ZM130.135 210.1C122.935 225.5 110.335 233 94.5351 233.5C71.8351 233.5 54.1351 217.5 51.9351 195.9C49.9351 176.7 57.9351 160.2 73.7351 151.9C97.4351 139.3 128.735 150.9 133.735 182.4C135.135 191.9 134.235 201.3 130.135 210.1Z"/>' +
                    '</g>' +
                '</svg>';

                const contentHtml = `
                    <div class="amwal-conflict-modal-body">
                        <div class="amwal-conflict-brand-header">
                            ${amwalLogoSvg}
                        </div>
                        <h3 class="amwal-conflict-title">${$t('Shopping Cart')}</h3>
                        <p class="amwal-conflict-text">${$t('Your cart already contains items. Would you like to continue with your current cart or clear the cart and add this item?')}</p>
                        ${itemsHtml}
                    </div>
                `;

                const bindScrollHint = (modalPopup) => {
                    const listEl = modalPopup.find('.amwal-cart-items-list');
                    const hintEl = modalPopup.find('.amwal-cart-scroll-hint');
                    if (listEl.length && hintEl.length) {
                        listEl.off('scroll.amwalHint').on('scroll.amwalHint', function () {
                            if (this.scrollTop + this.clientHeight >= this.scrollHeight - 12) {
                                hintEl.css('opacity', '0');
                            } else {
                                hintEl.css('opacity', '1');
                            }
                        });
                    }
                };

                confirm({
                    title: $t('Shopping Cart'),
                    content: contentHtml,
                    modalClass: 'amwal-cart-conflict-modal',
                    buttons: [
                        {
                            text: $t('Continue with current cart'),
                            class: 'action-primary action-continue',
                            click: function (event) {
                                this.closeModal(event, true);
                                self.productButtonContainer.setAttribute('data-empty-cart-on-cancellation', 'false');
                                bypassingModal = true;
                                amwalCheckoutButton.click();
                            }
                        },
                        {
                            text: $t('Clear cart and add this item'),
                            class: 'action-secondary action-clear-cart',
                            click: function (event) {
                                this.closeModal(event, true);
                                self.productButtonContainer.setAttribute('data-empty-cart-on-cancellation', 'false');
                                $('body').trigger('processStart');
                                cleanQuote().always(function () {
                                    $('body').trigger('processStop');
                                    customerData.invalidate(['cart']);
                                    customerData.reload(['cart'], true);
                                    bypassingModal = true;
                                    amwalCheckoutButton.click();
                                });
                            }
                        }
                    ],
                    actions: {
                        cancel: function () {
                            // User dismissed modal
                        }
                    },
                    opened: function () {
                        const modalPopup = $('.modal-popup.amwal-cart-conflict-modal');
                        const overlay = $('.modals-overlay');
                        if (modalPopup.length && overlay.length) {
                            const overlayZ = parseInt(overlay.css('z-index'), 10) || 900;
                            modalPopup.css('z-index', overlayZ + 2);
                        }
                        bindScrollHint(modalPopup);
                    }
                });

                setTimeout(function () {
                    const modalPopup = $('.modal-popup.amwal-cart-conflict-modal');
                    const overlay = $('.modals-overlay');
                    if (modalPopup.length && overlay.length) {
                        const overlayZ = parseInt(overlay.css('z-index'), 10) || 900;
                        modalPopup.css('z-index', overlayZ + 2);
                    }
                    bindScrollHint(modalPopup);
                }, 50);
            };

            /**
             * Attach capture-phase click listener to intercept click when cart has items (product page only)
             */
            const attachButtonClickListener = (amwalCheckoutButton) => {
                if (!isProductDetailPage) {
                    return;
                }

                self.productButtonContainer.addEventListener('click', function (event) {
                    if (bypassingModal) {
                        bypassingModal = false;
                        return;
                    }

                    if (!isProductFormValid()) {
                        event.preventDefault();
                        event.stopPropagation();
                        event.stopImmediatePropagation();
                        addToCartForm.validation('isValid');
                        return;
                    }

                    if (isProductDetailPage && !isCartEmpty()) {
                        event.preventDefault();
                        event.stopPropagation();
                        event.stopImmediatePropagation();
                        showCartConflictModal(amwalCheckoutButton);
                    }
                }, true);
            };
        },

        /**
         * Initialize mini cart specific actions
         */
        initializeMiniCart: function () {
            let self = this;

            const proceedToCheckoutObserver = new MutationObserver((mutations) => {
                const proceedToCheckoutButton = document.getElementById('top-cart-btn-checkout')
                if (proceedToCheckoutButton){
                    self.productButtonContainer.classList.remove('hidden')
                    proceedToCheckoutButton.after(self.productButtonContainer)
                    if (self.hideProceedToCheckout) {
                        proceedToCheckoutButton.style.display = 'none'
                    }
                    proceedToCheckoutObserver.disconnect()
                }
            })
            proceedToCheckoutObserver.observe(document.getElementById('minicart-content-wrapper'), {
                childList: true,
                subtree: true,
                attributes: false,
                characterData: false
            });
        },
        /**
         * Initialize login specific actions
         */
        initializeLogin: function () {
            let self = this;
            /**
             * Check if the cart is empty
             * @return Boolean
             */
            const isCartEmpty = () => {
                const cart = customerData.get('cart');
                return !cart().summary_count;
            }
            const loginButton = document.querySelector('.amwal-express-checkout-button.login');
            const or = document.querySelector('.amwal-express-checkout-or');
            if (!isCartEmpty()) {
                or.classList.remove('hidden');
                loginButton.classList.remove('hidden');
            }
        }
    });
});
