/*! For license information please see 7108.bundle.js.LICENSE.txt */
"use strict";(self.webpackChunkamwal_magento_react=self.webpackChunkamwal_magento_react||[]).push([[7108,3617],{6284:function(e,t,n){n.d(t,{C:function(){return a},a:function(){return o},d:function(){return i}});var r=n(614);const o=async(e,t,n,o,i,a)=>{var s;if(e)return e.attachViewToDom(t,n,i,o);if(!(a||"string"==typeof n||n instanceof HTMLElement))throw new Error("framework delegate is missing");const l="string"==typeof n?null===(s=t.ownerDocument)||void 0===s?void 0:s.createElement(n):n;return o&&o.forEach((e=>l.classList.add(e))),i&&Object.assign(l,i),t.appendChild(l),await new Promise((e=>(0,r.c)(l,e))),l},i=(e,t)=>{if(t){if(e){const n=t.parentElement;return e.removeViewFromDom(n,t)}t.remove()}return Promise.resolve()},a=()=>{let e,t;return{attachViewToDom:async(n,o,i={},a=[])=>{var s,l;if(e=n,o){const t="string"==typeof o?null===(s=e.ownerDocument)||void 0===s?void 0:s.createElement(o):o;a.forEach((e=>t.classList.add(e))),Object.assign(t,i),e.appendChild(t),await new Promise((e=>(0,r.c)(t,e)))}else if(e.children.length>0&&("ION-MODAL"===e.tagName||"ION-POPOVER"===e.tagName)&&!e.children[0].classList.contains("ion-delegate-host")){const t=null===(l=e.ownerDocument)||void 0===l?void 0:l.createElement("div");t.classList.add("ion-delegate-host"),a.forEach((e=>t.classList.add(e))),t.append(...e.children),e.appendChild(t)}const c=document.querySelector("ion-app")||document.body;return t=document.createComment("ionic teleport"),e.parentNode.insertBefore(t,e),c.appendChild(e),e},removeViewFromDom:()=>(e&&t&&(t.parentNode.insertBefore(e,t),t.remove()),Promise.resolve())}}},3617:function(e,t,n){n.r(t),n.d(t,{MENU_BACK_BUTTON_PRIORITY:function(){return a},OVERLAY_BACK_BUTTON_PRIORITY:function(){return i},blockHardwareBackButton:function(){return r},startHardwareBackButton:function(){return o}});const r=()=>{document.addEventListener("backbutton",(()=>{}))},o=()=>{const e=document;let t=!1;e.addEventListener("backbutton",(()=>{if(t)return;let n=0,r=[];const o=new CustomEvent("ionBackButton",{bubbles:!1,detail:{register(e,t){r.push({priority:e,handler:t,id:n++})}}});e.dispatchEvent(o);const i=()=>{if(r.length>0){let e={priority:Number.MIN_SAFE_INTEGER,handler:()=>{},id:-1};r.forEach((t=>{t.priority>=e.priority&&(e=t)})),t=!0,r=r.filter((t=>t.id!==e.id)),(async e=>{try{if(null==e?void 0:e.handler){const t=e.handler(i);null!=t&&await t}}catch(e){console.error(e)}})(e).then((()=>t=!1))}};i()}))},i=100,a=99},614:function(e,t,n){n.d(t,{a:function(){return d},b:function(){return f},c:function(){return i},d:function(){return s},e:function(){return b},f:function(){return p},g:function(){return m},h:function(){return v},i:function(){return c},j:function(){return y},k:function(){return A},l:function(){return h},m:function(){return _},n:function(){return g},o:function(){return a},p:function(){return w},q:function(){return L},r:function(){return u},s:function(){return x},t:function(){return r},u:function(){return E},v:function(){return k}});const r=(e,t=0)=>new Promise((n=>{o(e,t,n)})),o=(e,t=0,n)=>{let r,o;const i={passive:!0},a=()=>{r&&r()},s=t=>{void 0!==t&&e!==t.target||(a(),n(t))};return e&&(e.addEventListener("webkitTransitionEnd",s,i),e.addEventListener("transitionend",s,i),o=setTimeout(s,t+500),r=()=>{o&&(clearTimeout(o),o=void 0),e.removeEventListener("webkitTransitionEnd",s,i),e.removeEventListener("transitionend",s,i)}),a},i=(e,t)=>{e.componentOnReady?e.componentOnReady().then((e=>t(e))):f((()=>t(e)))},a=e=>void 0!==e.componentOnReady,s=(e,t=[])=>{const n={};return t.forEach((t=>{e.hasAttribute(t)&&(null!==e.getAttribute(t)&&(n[t]=e.getAttribute(t)),e.removeAttribute(t))})),n},l=["role","aria-activedescendant","aria-atomic","aria-autocomplete","aria-braillelabel","aria-brailleroledescription","aria-busy","aria-checked","aria-colcount","aria-colindex","aria-colindextext","aria-colspan","aria-controls","aria-current","aria-describedby","aria-description","aria-details","aria-disabled","aria-errormessage","aria-expanded","aria-flowto","aria-haspopup","aria-hidden","aria-invalid","aria-keyshortcuts","aria-label","aria-labelledby","aria-level","aria-live","aria-multiline","aria-multiselectable","aria-orientation","aria-owns","aria-placeholder","aria-posinset","aria-pressed","aria-readonly","aria-relevant","aria-required","aria-roledescription","aria-rowcount","aria-rowindex","aria-rowindextext","aria-rowspan","aria-selected","aria-setsize","aria-sort","aria-valuemax","aria-valuemin","aria-valuenow","aria-valuetext"],c=(e,t)=>{let n=l;return t&&t.length>0&&(n=n.filter((e=>!t.includes(e)))),s(e,n)},d=(e,t,n,r)=>{var o;if("undefined"!=typeof window){const i=window,a=null===(o=null==i?void 0:i.Ionic)||void 0===o?void 0:o.config;if(a){const o=a.get("_ael");if(o)return o(e,t,n,r);if(a._ael)return a._ael(e,t,n,r)}}return e.addEventListener(t,n,r)},u=(e,t,n,r)=>{var o;if("undefined"!=typeof window){const i=window,a=null===(o=null==i?void 0:i.Ionic)||void 0===o?void 0:o.config;if(a){const o=a.get("_rel");if(o)return o(e,t,n,r);if(a._rel)return a._rel(e,t,n,r)}}return e.removeEventListener(t,n,r)},m=(e,t=e)=>e.shadowRoot||t,f=e=>"function"==typeof __zone_symbol__requestAnimationFrame?__zone_symbol__requestAnimationFrame(e):"function"==typeof requestAnimationFrame?requestAnimationFrame(e):setTimeout(e),v=e=>!!e.shadowRoot&&!!e.attachShadow,h=e=>{const t=e.closest("ion-item");return t?t.querySelector("ion-label"):null},p=e=>{if(e.focus(),e.classList.contains("ion-focusable")){const t=e.closest("ion-app");t&&t.setFocus([e])}},w=(e,t)=>{let n;const r=e.getAttribute("aria-labelledby"),o=e.id;let i=null!==r&&""!==r.trim()?r:t+"-lbl",a=null!==r&&""!==r.trim()?document.getElementById(r):h(e);return a?(null===r&&(a.id=i),n=a.textContent,a.setAttribute("aria-hidden","true")):""!==o.trim()&&(a=document.querySelector(`label[for="${o}"]`),a&&(""!==a.id?i=a.id:a.id=i=`${o}-lbl`,n=a.textContent)),{label:a,labelId:i,labelText:n}},b=(e,t,n,r,o)=>{if(e||v(t)){let e=t.querySelector("input.aux-input");e||(e=t.ownerDocument.createElement("input"),e.type="hidden",e.classList.add("aux-input"),t.appendChild(e)),e.disabled=o,e.name=n,e.value=r||""}},y=(e,t,n)=>Math.max(e,Math.min(t,n)),g=(e,t)=>{if(!e){const e="ASSERT: "+t;throw console.error(e),new Error(e)}},E=e=>e.timeStamp||Date.now(),k=e=>{if(e){const t=e.changedTouches;if(t&&t.length>0){const e=t[0];return{x:e.clientX,y:e.clientY}}if(void 0!==e.pageX)return{x:e.pageX,y:e.pageY}}return{x:0,y:0}},_=e=>{const t="rtl"===document.dir;switch(e){case"start":return t;case"end":return!t;default:throw new Error(`"${e}" is not a valid value for [side]. Use "start" or "end" instead.`)}},A=(e,t)=>{const n=e._original||e;return{_original:e,emit:L(n.emit.bind(n),t)}},L=(e,t=0)=>{let n;return(...r)=>{clearTimeout(n),n=setTimeout(e,t,...r)}},x=(e,t)=>{if(null!=e||(e={}),null!=t||(t={}),e===t)return!0;const n=Object.keys(e);if(n.length!==Object.keys(t).length)return!1;for(const r of n){if(!(r in t))return!1;if(e[r]!==t[r])return!1}return!0}},1983:function(e,t,n){n.d(t,{a:function(){return o},b:function(){return i},p:function(){return r}});const r=(e,...t)=>console.warn(`[Ionic Warning]: ${e}`,...t),o=(e,...t)=>console.error(`[Ionic Error]: ${e}`,...t),i=(e,...t)=>console.error(`<${e.tagName.toLowerCase()}> must be used inside ${t.join(" or ")}.`)},7108:function(e,t,n){n.d(t,{B:function(){return R},G:function(){return F},a:function(){return j},b:function(){return x},c:function(){return N},d:function(){return D},e:function(){return S},f:function(){return d},g:function(){return A},h:function(){return P},i:function(){return q},j:function(){return h},k:function(){return f},l:function(){return m},m:function(){return v},n:function(){return g},p:function(){return p},s:function(){return w}});var r=n(1209),o=n(6284),i=n(3617),a=n(614),s=n(1983);let l=0,c=0;const d=new WeakMap,u=e=>({create(t){return b(e,t)},dismiss(t,n,r){return _(document,t,n,e,r)},async getTop(){return A(document,e)}}),m=u("ion-alert"),f=u("ion-action-sheet"),v=u("ion-modal"),h=u("ion-popover"),p=e=>{"undefined"!=typeof document&&k(document);const t=l++;e.overlayIndex=t},w=e=>(e.hasAttribute("id")||(e.id="ion-overlay-"+ ++c),e.id),b=(e,t)=>"undefined"!=typeof window&&void 0!==window.customElements?window.customElements.whenDefined(e).then((()=>{const n=document.createElement(e);return n.classList.add("overlay-hidden"),Object.assign(n,Object.assign(Object.assign({},t),{hasController:!0})),O(document).appendChild(n),new Promise((e=>(0,a.c)(n,e)))})):Promise.resolve(),y='[tabindex]:not([tabindex^="-"]):not([hidden]):not([disabled]), input:not([type=hidden]):not([tabindex^="-"]):not([hidden]):not([disabled]), textarea:not([tabindex^="-"]):not([hidden]):not([disabled]), button:not([tabindex^="-"]):not([hidden]):not([disabled]), select:not([tabindex^="-"]):not([hidden]):not([disabled]), .ion-focusable:not([tabindex^="-"]):not([hidden]):not([disabled]), .ion-focusable[disabled="false"]:not([tabindex^="-"]):not([hidden])',g=(e,t)=>{let n=e.querySelector(y);const r=null==n?void 0:n.shadowRoot;r&&(n=r.querySelector(y)||n),n?(0,a.f)(n):t.focus()},E=(e,t)=>{const n=Array.from(e.querySelectorAll(y));let r=n.length>0?n[n.length-1]:null;const o=null==r?void 0:r.shadowRoot;o&&(r=o.querySelector(y)||r),r?r.focus():t.focus()},k=e=>{0===l&&(l=1,e.addEventListener("focus",(t=>{((e,t)=>{const n=A(t,"ion-alert,ion-action-sheet,ion-loading,ion-modal,ion-picker,ion-popover"),r=e.target;n&&r&&(n.classList.contains("ion-disable-focus-trap")||(n.shadowRoot?(()=>{if(n.contains(r))n.lastFocus=r;else{const e=n.lastFocus;g(n,n),e===t.activeElement&&E(n,n),n.lastFocus=t.activeElement}})():(()=>{if(n===r)n.lastFocus=void 0;else{const e=(0,a.g)(n);if(!e.contains(r))return;const o=e.querySelector(".ion-overlay-wrapper");if(!o)return;if(o.contains(r)||r===e.querySelector("ion-backdrop"))n.lastFocus=r;else{const e=n.lastFocus;g(o,n),e===t.activeElement&&E(o,n),n.lastFocus=t.activeElement}}})()))})(t,e)}),!0),e.addEventListener("ionBackButton",(t=>{const n=A(e);(null==n?void 0:n.backdropDismiss)&&t.detail.register(i.OVERLAY_BACK_BUTTON_PRIORITY,(()=>n.dismiss(void 0,R)))})),e.addEventListener("keydown",(t=>{if("Escape"===t.key){const t=A(e);(null==t?void 0:t.backdropDismiss)&&t.dismiss(void 0,R)}})))},_=(e,t,n,r,o)=>{const i=A(e,r,o);return i?i.dismiss(t,n):Promise.reject("overlay does not exist")},A=(e,t,n)=>{const r=((e,t)=>(void 0===t&&(t="ion-alert,ion-action-sheet,ion-loading,ion-modal,ion-picker,ion-popover,ion-toast"),Array.from(e.querySelectorAll(t)).filter((e=>e.overlayIndex>0))))(e,t).filter((e=>!e.classList.contains("overlay-hidden")));return void 0===n?r[r.length-1]:r.find((e=>e.id===n))},L=(e=!1)=>{const t=O(document).querySelector("ion-router-outlet, ion-nav, #ion-view-container-root");t&&(e?t.setAttribute("aria-hidden","true"):t.removeAttribute("aria-hidden"))},x=async(e,t,n,o,i)=>{var a,s;if(e.presented)return;L(!0),e.presented=!0,e.willPresent.emit(),null===(a=e.willPresentShorthand)||void 0===a||a.emit();const l=(0,r.g)(e),c=e.enterAnimation?e.enterAnimation:r.c.get(t,"ios"===l?n:o);await C(e,c,e.el,i)&&(e.didPresent.emit(),null===(s=e.didPresentShorthand)||void 0===s||s.emit()),"ION-TOAST"!==e.el.tagName&&T(e.el),!e.keyboardClose||null!==document.activeElement&&e.el.contains(document.activeElement)||e.el.focus()},T=async e=>{let t=document.activeElement;if(!t)return;const n=null==t?void 0:t.shadowRoot;n&&(t=n.querySelector(y)||t),await e.onDidDismiss(),t.focus()},D=async(e,t,n,o,i,a,s)=>{var l,c;if(!e.presented)return!1;L(!1),e.presented=!1;try{e.el.style.setProperty("pointer-events","none"),e.willDismiss.emit({data:t,role:n}),null===(l=e.willDismissShorthand)||void 0===l||l.emit({data:t,role:n});const u=(0,r.g)(e),m=e.leaveAnimation?e.leaveAnimation:r.c.get(o,"ios"===u?i:a);n!==F&&await C(e,m,e.el,s),e.didDismiss.emit({data:t,role:n}),null===(c=e.didDismissShorthand)||void 0===c||c.emit({data:t,role:n}),d.delete(e),e.el.classList.add("overlay-hidden"),e.el.style.removeProperty("pointer-events"),void 0!==e.el.lastFocus&&(e.el.lastFocus=void 0)}catch(e){console.error(e)}return e.el.remove(),!0},O=e=>e.querySelector("ion-app")||e.body,C=async(e,t,n,o)=>{n.classList.remove("overlay-hidden");const i=t(e.el,o);e.animated&&r.c.getBoolean("animated",!0)||i.duration(0),e.keyboardClose&&i.beforeAddWrite((()=>{const e=n.ownerDocument.activeElement;(null==e?void 0:e.matches("input,ion-input, ion-textarea"))&&e.blur()}));const a=d.get(e)||[];return d.set(e,[...a,i]),await i.play(),!0},S=(e,t)=>{let n;const r=new Promise((e=>n=e));return I(e,t,(e=>{n(e.detail)})),r},I=(e,t,n)=>{const r=o=>{(0,a.r)(e,t,r),n(o)};(0,a.a)(e,t,r)},q=e=>"cancel"===e||e===R,B=e=>e(),P=(e,t)=>{if("function"==typeof e)return r.c.get("_zoneGate",B)((()=>{try{return e(t)}catch(e){throw e}}))},R="backdrop",F="gesture",N=e=>{let t,n=!1;const r=(0,o.C)(),i=(o=!1)=>{if(t&&!o)return{delegate:t,inline:n};const{el:i,hasController:a,delegate:s}=e,l=i.parentNode;return n=null!==l&&!a,t=n?s||r:s,{inline:n,delegate:t}};return{attachViewToDom:async t=>{const{delegate:n}=i(!0);if(n)return await n.attachViewToDom(e.el,t);const{hasController:r}=e;if(r&&void 0!==t)throw new Error("framework delegate is missing");return null},removeViewFromDom:()=>{const{delegate:t}=i();t&&void 0!==e.el&&t.removeViewFromDom(e.el.parentElement,e.el)}}},j=()=>{let e;const t=()=>{e&&(e(),e=void 0)};return{addClickListener:(n,r)=>{t();const o=void 0!==r?document.getElementById(r):null;o?e=((e,t)=>{const n=()=>{t.present()};return e.addEventListener("click",n),()=>{e.removeEventListener("click",n)}})(o,n):(0,s.p)(`A trigger element with the ID "${r}" was not found in the DOM. The trigger element must be in the DOM when the "trigger" property is set on an overlay component.`,n)},removeClickListener:t}}}}]);