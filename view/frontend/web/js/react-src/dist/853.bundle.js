/*! For license information please see 853.bundle.js.LICENSE.txt */
"use strict";(self.webpackChunkamwal_magento_react=self.webpackChunkamwal_magento_react||[]).push([[853],{8248:function(t,e,n){n.d(e,{a:function(){return c},b:function(){return f},c:function(){return i},d:function(){return a},e:function(){return b},f:function(){return p},g:function(){return d},h:function(){return m},i:function(){return l},j:function(){return v},k:function(){return C},l:function(){return g},m:function(){return E},n:function(){return y},o:function(){return s},p:function(){return w},q:function(){return L},r:function(){return h},s:function(){return k},t:function(){return r},u:function(){return R},v:function(){return S}});const r=(t,e=0)=>new Promise((n=>{o(t,e,n)})),o=(t,e=0,n)=>{let r,o;const i={passive:!0},s=()=>{r&&r()},a=e=>{void 0!==e&&t!==e.target||(s(),n(e))};return t&&(t.addEventListener("webkitTransitionEnd",a,i),t.addEventListener("transitionend",a,i),o=setTimeout(a,e+500),r=()=>{o&&(clearTimeout(o),o=void 0),t.removeEventListener("webkitTransitionEnd",a,i),t.removeEventListener("transitionend",a,i)}),s},i=(t,e)=>{t.componentOnReady?t.componentOnReady().then((t=>e(t))):f((()=>e(t)))},s=t=>void 0!==t.componentOnReady,a=(t,e=[])=>{const n={};return e.forEach((e=>{t.hasAttribute(e)&&(null!==t.getAttribute(e)&&(n[e]=t.getAttribute(e)),t.removeAttribute(e))})),n},u=["role","aria-activedescendant","aria-atomic","aria-autocomplete","aria-braillelabel","aria-brailleroledescription","aria-busy","aria-checked","aria-colcount","aria-colindex","aria-colindextext","aria-colspan","aria-controls","aria-current","aria-describedby","aria-description","aria-details","aria-disabled","aria-errormessage","aria-expanded","aria-flowto","aria-haspopup","aria-hidden","aria-invalid","aria-keyshortcuts","aria-label","aria-labelledby","aria-level","aria-live","aria-multiline","aria-multiselectable","aria-orientation","aria-owns","aria-placeholder","aria-posinset","aria-pressed","aria-readonly","aria-relevant","aria-required","aria-roledescription","aria-rowcount","aria-rowindex","aria-rowindextext","aria-rowspan","aria-selected","aria-setsize","aria-sort","aria-valuemax","aria-valuemin","aria-valuenow","aria-valuetext"],l=(t,e)=>{let n=u;return e&&e.length>0&&(n=n.filter((t=>!e.includes(t)))),a(t,n)},c=(t,e,n,r)=>{var o;if("undefined"!=typeof window){const i=window,s=null===(o=null==i?void 0:i.Ionic)||void 0===o?void 0:o.config;if(s){const o=s.get("_ael");if(o)return o(t,e,n,r);if(s._ael)return s._ael(t,e,n,r)}}return t.addEventListener(e,n,r)},h=(t,e,n,r)=>{var o;if("undefined"!=typeof window){const i=window,s=null===(o=null==i?void 0:i.Ionic)||void 0===o?void 0:o.config;if(s){const o=s.get("_rel");if(o)return o(t,e,n,r);if(s._rel)return s._rel(t,e,n,r)}}return t.removeEventListener(e,n,r)},d=(t,e=t)=>t.shadowRoot||e,f=t=>"function"==typeof __zone_symbol__requestAnimationFrame?__zone_symbol__requestAnimationFrame(t):"function"==typeof requestAnimationFrame?requestAnimationFrame(t):setTimeout(t),m=t=>!!t.shadowRoot&&!!t.attachShadow,g=t=>{const e=t.closest("ion-item");return e?e.querySelector("ion-label"):null},p=t=>{if(t.focus(),t.classList.contains("ion-focusable")){const e=t.closest("ion-app");e&&e.setFocus([t])}},w=(t,e)=>{let n;const r=t.getAttribute("aria-labelledby"),o=t.id;let i=null!==r&&""!==r.trim()?r:e+"-lbl",s=null!==r&&""!==r.trim()?document.getElementById(r):g(t);return s?(null===r&&(s.id=i),n=s.textContent,s.setAttribute("aria-hidden","true")):""!==o.trim()&&(s=document.querySelector(`label[for="${o}"]`),s&&(""!==s.id?i=s.id:s.id=i=`${o}-lbl`,n=s.textContent)),{label:s,labelId:i,labelText:n}},b=(t,e,n,r,o)=>{if(t||m(e)){let t=e.querySelector("input.aux-input");t||(t=e.ownerDocument.createElement("input"),t.type="hidden",t.classList.add("aux-input"),e.appendChild(t)),t.disabled=o,t.name=n,t.value=r||""}},v=(t,e,n)=>Math.max(t,Math.min(e,n)),y=(t,e)=>{if(!t){const t="ASSERT: "+e;throw console.error(t),new Error(t)}},S=t=>t.timeStamp||Date.now(),R=t=>{if(t){const e=t.changedTouches;if(e&&e.length>0){const t=e[0];return{x:t.clientX,y:t.clientY}}if(void 0!==t.pageX)return{x:t.pageX,y:t.pageY}}return{x:0,y:0}},E=t=>{const e="rtl"===document.dir;switch(t){case"start":return e;case"end":return!e;default:throw new Error(`"${t}" is not a valid value for [side]. Use "start" or "end" instead.`)}},C=(t,e)=>{const n=t._original||t;return{_original:t,emit:L(n.emit.bind(n),e)}},L=(t,e=0)=>{let n;return(...r)=>{clearTimeout(n),n=setTimeout(t,e,...r)}},k=(t,e)=>{if(null!=t||(t={}),null!=e||(e={}),t===e)return!0;const n=Object.keys(t);if(n.length!==Object.keys(e).length)return!1;for(const r of n){if(!(r in e))return!1;if(t[r]!==e[r])return!1}return!0}},853:function(t,e,n){n.r(e),n.d(e,{ion_router:function(){return L}});var r=n(2170),o=n(8248);const i="root",s="forward",a=t=>"/"+t.filter((t=>t.length>0)).join("/"),u=t=>{let e,n=[""];if(null!=t){const r=t.indexOf("?");r>-1&&(e=t.substring(r+1),t=t.substring(0,r)),n=t.split("/").map((t=>t.trim())).filter((t=>t.length>0)),0===n.length&&(n=[""])}return{segments:n,queryString:e}},l=async(t,e,n,r,s=!1,a)=>{try{const u=h(t);if(r>=e.length||!u)return s;await new Promise((t=>(0,o.c)(u,t)));const c=e[r],d=await u.setRouteId(c.id,c.params,n,a);return d.changed&&(n=i,s=!0),s=await l(d.element,e,n,r+1,s,a),d.markVisible&&await d.markVisible(),s}catch(t){return console.error(t),!1}},c=":not([no-router]) ion-nav, :not([no-router]) ion-tabs, :not([no-router]) ion-router-outlet",h=t=>{if(!t)return;if(t.matches(c))return t;const e=t.querySelector(c);return null!=e?e:void 0},d=(t,e)=>e.find((e=>((t,e)=>{const{from:n,to:r}=e;if(void 0===r)return!1;if(n.length>t.length)return!1;for(let e=0;e<n.length;e++){const r=n[e];if("*"===r)return!0;if(r!==t[e])return!1}return n.length===t.length})(t,e))),f=(t,e)=>{const n=Math.min(t.length,e.length);let r=0;for(let o=0;o<n;o++){const n=t[o],i=e[o];if(n.id.toLowerCase()!==i.id)break;if(n.params){const t=Object.keys(n.params);if(t.length===i.segments.length){const e=t.map((t=>`:${t}`));for(let t=0;t<e.length&&e[t].toLowerCase()===i.segments[t];t++)r++}}r++}return r},m=(t,e)=>{const n=new b(t);let r,o=!1;for(let t=0;t<e.length;t++){const i=e[t].segments;if(""===i[0])o=!0;else{for(const e of i){const o=n.next();if(":"===e[0]){if(""===o)return null;r=r||[],(r[t]||(r[t]={}))[e.slice(1)]=o}else if(o!==e)return null}o=!1}}return o&&o!==(""===n.next())?null:r?e.map(((t,e)=>({id:t.id,segments:t.segments,params:g(t.params,r[e]),beforeEnter:t.beforeEnter,beforeLeave:t.beforeLeave}))):e},g=(t,e)=>t||e?Object.assign(Object.assign({},t),e):void 0,p=(t,e)=>{let n=null,r=0;for(const o of e){const e=m(t,o);if(null!==e){const t=w(e);t>r&&(r=t,n=e)}}return n},w=t=>{let e=1,n=1;for(const r of t)for(const t of r.segments)":"===t[0]?e+=Math.pow(1,n):""!==t&&(e+=Math.pow(2,n)),n++;return e};class b{constructor(t){this.segments=t.slice()}next(){return this.segments.length>0?this.segments.shift():""}}const v=(t,e)=>e in t?t[e]:t.hasAttribute(e)?t.getAttribute(e):null,y=t=>Array.from(t.children).filter((t=>"ION-ROUTE-REDIRECT"===t.tagName)).map((t=>{const e=v(t,"to");return{from:u(v(t,"from")).segments,to:null==e?void 0:u(e)}})),S=t=>E(R(t)),R=t=>Array.from(t.children).filter((t=>"ION-ROUTE"===t.tagName&&t.component)).map((t=>{const e=v(t,"component");return{segments:u(v(t,"url")).segments,id:e.toLowerCase(),params:t.componentProps,beforeLeave:t.beforeLeave,beforeEnter:t.beforeEnter,children:R(t)}})),E=t=>{const e=[];for(const n of t)C([],e,n);return e},C=(t,e,n)=>{if(t=[...t,{id:n.id,segments:n.segments,params:n.params,beforeLeave:n.beforeLeave,beforeEnter:n.beforeEnter}],0!==n.children.length)for(const r of n.children)C(t,e,r);else e.push(t)},L=class{constructor(t){(0,r.r)(this,t),this.ionRouteWillChange=(0,r.d)(this,"ionRouteWillChange",7),this.ionRouteDidChange=(0,r.d)(this,"ionRouteDidChange",7),this.previousPath=null,this.busy=!1,this.state=0,this.lastState=0,this.root="/",this.useHash=!0}async componentWillLoad(){await(h(document.body)?Promise.resolve():new Promise((t=>{window.addEventListener("ionNavWillLoad",(()=>t()),{once:!0})})));const t=await this.runGuards(this.getSegments());if(!0!==t){if("object"==typeof t){const{redirect:e}=t,n=u(e);this.setSegments(n.segments,i,n.queryString),await this.writeNavStateRoot(n.segments,i)}}else await this.onRoutesChanged()}componentDidLoad(){window.addEventListener("ionRouteRedirectChanged",(0,o.q)(this.onRedirectChanged.bind(this),10)),window.addEventListener("ionRouteDataChanged",(0,o.q)(this.onRoutesChanged.bind(this),100))}async onPopState(){const t=this.historyDirection();let e=this.getSegments();const n=await this.runGuards(e);if(!0!==n){if("object"!=typeof n)return!1;e=u(n.redirect).segments}return this.writeNavStateRoot(e,t)}onBackButton(t){t.detail.register(0,(t=>{this.back(),t()}))}async canTransition(){const t=await this.runGuards();return!0===t||"object"==typeof t&&t.redirect}async push(t,e="forward",n){var r;if(t.startsWith(".")){const e=null!==(r=this.previousPath)&&void 0!==r?r:"/",n=new URL(t,`https://host/${e}`);t=n.pathname+n.search}let o=u(t);const i=await this.runGuards(o.segments);if(!0!==i){if("object"!=typeof i)return!1;o=u(i.redirect)}return this.setSegments(o.segments,e,o.queryString),this.writeNavStateRoot(o.segments,e,n)}back(){return window.history.back(),Promise.resolve(this.waitPromise)}async printDebug(){(t=>{console.group(`[ion-core] ROUTES[${t.length}]`);for(const e of t){const t=[];e.forEach((e=>t.push(...e.segments)));const n=e.map((t=>t.id));console.debug(`%c ${a(t)}`,"font-weight: bold; padding-left: 20px","=>\t",`(${n.join(", ")})`)}console.groupEnd()})(S(this.el)),(t=>{console.group(`[ion-core] REDIRECTS[${t.length}]`);for(const e of t)e.to&&console.debug("FROM: ",`$c ${a(e.from)}`,"font-weight: bold"," TO: ",`$c ${a(e.to.segments)}`,"font-weight: bold");console.groupEnd()})(y(this.el))}async navChanged(t){if(this.busy)return console.warn("[ion-router] router is busy, navChanged was cancelled"),!1;const{ids:e,outlet:n}=await(async t=>{const e=[];let n,r=window.document.body;for(;n=h(r);){const t=await n.getRouteId();if(!t)break;r=t.element,t.element=void 0,e.push(t)}return{ids:e,outlet:n}})(),r=((t,e)=>{let n=null,r=0;for(const o of e){const e=f(t,o);e>r&&(n=o,r=e)}return n?n.map(((e,n)=>{var r;return{id:e.id,segments:e.segments,params:g(e.params,null===(r=t[n])||void 0===r?void 0:r.params)}})):null})(e,S(this.el));if(!r)return console.warn("[ion-router] no matching URL for ",e.map((t=>t.id))),!1;const o=(t=>{const e=[];for(const n of t)for(const t of n.segments)if(":"===t[0]){const r=n.params&&n.params[t.slice(1)];if(!r)return null;e.push(r)}else""!==t&&e.push(t);return e})(r);return o?(this.setSegments(o,t),await this.safeWriteNavState(n,r,i,o,null,e.length),!0):(console.warn("[ion-router] router could not match path because some required param is missing"),!1)}onRedirectChanged(){const t=this.getSegments();t&&d(t,y(this.el))&&this.writeNavStateRoot(t,i)}onRoutesChanged(){return this.writeNavStateRoot(this.getSegments(),i)}historyDirection(){var t;const e=window;null===e.history.state&&(this.state++,e.history.replaceState(this.state,e.document.title,null===(t=e.document.location)||void 0===t?void 0:t.href));const n=e.history.state,r=this.lastState;return this.lastState=n,n>r||n>=r&&r>0?s:n<r?"back":i}async writeNavStateRoot(t,e,n){if(!t)return console.error("[ion-router] URL is not part of the routing set"),!1;const r=y(this.el),o=d(t,r);let i=null;if(o){const{segments:n,queryString:r}=o.to;this.setSegments(n,e,r),i=o.from,t=n}const s=S(this.el),a=p(t,s);return a?this.safeWriteNavState(document.body,a,e,t,i,0,n):(console.error("[ion-router] the path does not match any route"),!1)}async safeWriteNavState(t,e,n,r,o,i=0,s){const a=await this.lock();let u=!1;try{u=await this.writeNavState(t,e,n,r,o,i,s)}catch(t){console.error(t)}return a(),u}async lock(){const t=this.waitPromise;let e;return this.waitPromise=new Promise((t=>e=t)),void 0!==t&&await t,e}async runGuards(t=this.getSegments(),e){if(void 0===e&&(e=u(this.previousPath).segments),!t||!e)return!0;const n=S(this.el),r=p(e,n),o=r&&r[r.length-1].beforeLeave,i=!o||await o();if(!1===i||"object"==typeof i)return i;const s=p(t,n),a=s&&s[s.length-1].beforeEnter;return!a||a()}async writeNavState(t,e,n,r,o,i=0,s){if(this.busy)return console.warn("[ion-router] router is busy, transition was cancelled"),!1;this.busy=!0;const a=this.routeChangeEvent(r,o);a&&this.ionRouteWillChange.emit(a);const u=await l(t,e,n,i,!1,s);return this.busy=!1,a&&this.ionRouteDidChange.emit(a),u}setSegments(t,e,n){this.state++,((t,e,n,r,o,i,l)=>{const c=((t,e,n)=>{let r=a(t);return e&&(r="#"+r),void 0!==n&&(r+="?"+n),r})([...u(e).segments,...r],n,l);o===s?t.pushState(i,"",c):t.replaceState(i,"",c)})(window.history,this.root,this.useHash,t,e,this.state,n)}getSegments(){return((t,e,n)=>{const r=u(e).segments,o=n?t.hash.slice(1):t.pathname;return((t,e)=>{if(t.length>e.length)return null;if(t.length<=1&&""===t[0])return e;for(let n=0;n<t.length;n++)if(t[n]!==e[n])return null;return e.length===t.length?[""]:e.slice(t.length)})(r,u(o).segments)})(window.location,this.root,this.useHash)}routeChangeEvent(t,e){const n=this.previousPath,r=a(t);return this.previousPath=r,r===n?null:{from:n,redirectedFrom:e?a(e):null,to:r}}get el(){return(0,r.e)(this)}}}}]);