/*! For license information please see 8866.bundle.js.LICENSE.txt */
"use strict";(self.webpackChunkamwal_magento_react=self.webpackChunkamwal_magento_react||[]).push([[8866],{8248:function(e,t,n){n.d(t,{a:function(){return c},b:function(){return m},c:function(){return i},d:function(){return l},e:function(){return y},f:function(){return p},g:function(){return f},h:function(){return v},i:function(){return u},j:function(){return h},k:function(){return L},l:function(){return w},m:function(){return x},n:function(){return g},o:function(){return a},p:function(){return b},q:function(){return T},r:function(){return d},s:function(){return _},t:function(){return o},u:function(){return S},v:function(){return E}});const o=(e,t=0)=>new Promise((n=>{r(e,t,n)})),r=(e,t=0,n)=>{let o,r;const i={passive:!0},a=()=>{o&&o()},l=t=>{void 0!==t&&e!==t.target||(a(),n(t))};return e&&(e.addEventListener("webkitTransitionEnd",l,i),e.addEventListener("transitionend",l,i),r=setTimeout(l,t+500),o=()=>{r&&(clearTimeout(r),r=void 0),e.removeEventListener("webkitTransitionEnd",l,i),e.removeEventListener("transitionend",l,i)}),a},i=(e,t)=>{e.componentOnReady?e.componentOnReady().then((e=>t(e))):m((()=>t(e)))},a=e=>void 0!==e.componentOnReady,l=(e,t=[])=>{const n={};return t.forEach((t=>{e.hasAttribute(t)&&(null!==e.getAttribute(t)&&(n[t]=e.getAttribute(t)),e.removeAttribute(t))})),n},s=["role","aria-activedescendant","aria-atomic","aria-autocomplete","aria-braillelabel","aria-brailleroledescription","aria-busy","aria-checked","aria-colcount","aria-colindex","aria-colindextext","aria-colspan","aria-controls","aria-current","aria-describedby","aria-description","aria-details","aria-disabled","aria-errormessage","aria-expanded","aria-flowto","aria-haspopup","aria-hidden","aria-invalid","aria-keyshortcuts","aria-label","aria-labelledby","aria-level","aria-live","aria-multiline","aria-multiselectable","aria-orientation","aria-owns","aria-placeholder","aria-posinset","aria-pressed","aria-readonly","aria-relevant","aria-required","aria-roledescription","aria-rowcount","aria-rowindex","aria-rowindextext","aria-rowspan","aria-selected","aria-setsize","aria-sort","aria-valuemax","aria-valuemin","aria-valuenow","aria-valuetext"],u=(e,t)=>{let n=s;return t&&t.length>0&&(n=n.filter((e=>!t.includes(e)))),l(e,n)},c=(e,t,n,o)=>{var r;if("undefined"!=typeof window){const i=window,a=null===(r=null==i?void 0:i.Ionic)||void 0===r?void 0:r.config;if(a){const r=a.get("_ael");if(r)return r(e,t,n,o);if(a._ael)return a._ael(e,t,n,o)}}return e.addEventListener(t,n,o)},d=(e,t,n,o)=>{var r;if("undefined"!=typeof window){const i=window,a=null===(r=null==i?void 0:i.Ionic)||void 0===r?void 0:r.config;if(a){const r=a.get("_rel");if(r)return r(e,t,n,o);if(a._rel)return a._rel(e,t,n,o)}}return e.removeEventListener(t,n,o)},f=(e,t=e)=>e.shadowRoot||t,m=e=>"function"==typeof __zone_symbol__requestAnimationFrame?__zone_symbol__requestAnimationFrame(e):"function"==typeof requestAnimationFrame?requestAnimationFrame(e):setTimeout(e),v=e=>!!e.shadowRoot&&!!e.attachShadow,w=e=>{const t=e.closest("ion-item");return t?t.querySelector("ion-label"):null},p=e=>{if(e.focus(),e.classList.contains("ion-focusable")){const t=e.closest("ion-app");t&&t.setFocus([e])}},b=(e,t)=>{let n;const o=e.getAttribute("aria-labelledby"),r=e.id;let i=null!==o&&""!==o.trim()?o:t+"-lbl",a=null!==o&&""!==o.trim()?document.getElementById(o):w(e);return a?(null===o&&(a.id=i),n=a.textContent,a.setAttribute("aria-hidden","true")):""!==r.trim()&&(a=document.querySelector(`label[for="${r}"]`),a&&(""!==a.id?i=a.id:a.id=i=`${r}-lbl`,n=a.textContent)),{label:a,labelId:i,labelText:n}},y=(e,t,n,o,r)=>{if(e||v(t)){let e=t.querySelector("input.aux-input");e||(e=t.ownerDocument.createElement("input"),e.type="hidden",e.classList.add("aux-input"),t.appendChild(e)),e.disabled=r,e.name=n,e.value=o||""}},h=(e,t,n)=>Math.max(e,Math.min(t,n)),g=(e,t)=>{if(!e){const e="ASSERT: "+t;throw console.error(e),new Error(e)}},E=e=>e.timeStamp||Date.now(),S=e=>{if(e){const t=e.changedTouches;if(t&&t.length>0){const e=t[0];return{x:e.clientX,y:e.clientY}}if(void 0!==e.pageX)return{x:e.pageX,y:e.pageY}}return{x:0,y:0}},x=e=>{const t="rtl"===document.dir;switch(e){case"start":return t;case"end":return!t;default:throw new Error(`"${e}" is not a valid value for [side]. Use "start" or "end" instead.`)}},L=(e,t)=>{const n=e._original||e;return{_original:e,emit:T(n.emit.bind(n),t)}},T=(e,t=0)=>{let n;return(...o)=>{clearTimeout(n),n=setTimeout(e,t,...o)}},_=(e,t)=>{if(null!=e||(e={}),null!=t||(t={}),e===t)return!0;const n=Object.keys(e);if(n.length!==Object.keys(t).length)return!1;for(const o of n){if(!(o in t))return!1;if(e[o]!==t[o])return!1}return!0}},1983:function(e,t,n){n.d(t,{a:function(){return r},b:function(){return i},p:function(){return o}});const o=(e,...t)=>console.warn(`[Ionic Warning]: ${e}`,...t),r=(e,...t)=>console.error(`[Ionic Error]: ${e}`,...t),i=(e,...t)=>console.error(`<${e.tagName.toLowerCase()}> must be used inside ${t.join(" or ")}.`)},6378:function(e,t,n){n.d(t,{I:function(){return a},a:function(){return d},b:function(){return i},c:function(){return m},d:function(){return w},f:function(){return c},g:function(){return u},i:function(){return s},p:function(){return v},r:function(){return p},s:function(){return f}});var o=n(8248),r=n(1983);const i="ion-content",a=".ion-content-scroll-host",l=`${i}, ${a}`,s=e=>"ION-CONTENT"===e.tagName,u=async e=>s(e)?(await new Promise((t=>(0,o.c)(e,t))),e.getScrollElement()):e,c=e=>e.querySelector(a)||e.querySelector(l),d=e=>e.closest(l),f=(e,t)=>s(e)?e.scrollToTop(t):Promise.resolve(e.scrollTo({top:0,left:0,behavior:t>0?"smooth":"auto"})),m=(e,t,n,o)=>s(e)?e.scrollByPoint(t,n,o):Promise.resolve(e.scrollBy({top:n,left:t,behavior:o>0?"smooth":"auto"})),v=e=>(0,r.b)(e,i),w=e=>{if(s(e)){const t=e,n=t.scrollY;return t.scrollY=!1,n}return e.style.setProperty("overflow","hidden"),!0},p=(e,t)=>{s(e)?e.scrollY=t:e.style.removeProperty("overflow")}},4314:function(e,t,n){n.d(t,{w:function(){return o}});const o="undefined"!=typeof window?window:void 0},8866:function(e,t,n){n.r(t),n.d(t,{startInputShims:function(){return y}});var o,r=n(6378),i=n(8248),a=n(4314);!function(e){e.Body="body",e.Ionic="ionic",e.Native="native",e.None="none"}(o||(o={}));const l={getEngine(){var e;return(null===(e=null===a.w||void 0===a.w?void 0:a.w.Capacitor)||void 0===e?void 0:e.isPluginAvailable("Keyboard"))&&(null===a.w||void 0===a.w?void 0:a.w.Capacitor.Plugins.Keyboard)},getResizeMode(){const e=this.getEngine();return e&&e.getResizeMode?e.getResizeMode():Promise.resolve(void 0)}},s=new WeakMap,u=(e,t,n,o=0,r=!1)=>{s.has(e)!==n&&(n?c(e,t,o,r):d(e,t))},c=(e,t,n,o=!1)=>{const r=t.parentNode,i=t.cloneNode(!1);i.classList.add("cloned-input"),i.tabIndex=-1,o&&(i.disabled=!0),r.appendChild(i),s.set(e,i);const a="rtl"===e.ownerDocument.dir?9999:-9999;e.style.pointerEvents="none",t.style.transform=`translate3d(${a}px,${n}px,0) scale(0)`},d=(e,t)=>{const n=s.get(e);n&&(s.delete(e),n.remove()),e.style.pointerEvents="",t.style.transform=""},f="input, textarea, [no-blur], [contenteditable]",m="$ionPaddingTimer",v=(e,t,n)=>{const o=e[m];o&&clearTimeout(o),t>0?e.style.setProperty("--keyboard-offset",`${t}px`):e[m]=setTimeout((()=>{e.style.setProperty("--keyboard-offset","0px"),n&&n()}),120)},w=(e,t,n)=>{e.addEventListener("focusout",(()=>{t&&v(t,0,n)}),{once:!0})};let p=0;const b=async(e,t,n,o,a,l,s=!1)=>{if(!n&&!o)return;const c=((e,t,n)=>{var o;return((e,t,n,o)=>{const r=e.top,i=e.bottom,a=t.top,l=a+15,s=Math.min(t.bottom,o-n)-50-i,u=l-r,c=Math.round(s<0?-s:u>0?-u:0),d=Math.min(c,r-a),f=Math.abs(d)/.3;return{scrollAmount:d,scrollDuration:Math.min(400,Math.max(150,f)),scrollPadding:n,inputSafeY:4-(r-l)}})((null!==(o=e.closest("ion-item,[ion-item]"))&&void 0!==o?o:e).getBoundingClientRect(),t.getBoundingClientRect(),n,e.ownerDocument.defaultView.innerHeight)})(e,n||o,a);if(n&&Math.abs(c.scrollAmount)<4)return t.focus(),void(l&&null!==n&&(p+=c.scrollPadding,v(n,p),w(t,n,(()=>p=0))));if(u(e,t,!0,c.inputSafeY,s),t.focus(),(0,i.b)((()=>e.click())),l&&n&&(p+=c.scrollPadding,v(n,p)),"undefined"!=typeof window){let o;const i=async()=>{void 0!==o&&clearTimeout(o),window.removeEventListener("ionKeyboardDidShow",a),window.removeEventListener("ionKeyboardDidShow",i),n&&await(0,r.c)(n,0,c.scrollAmount,c.scrollDuration),u(e,t,!1,c.inputSafeY),t.focus(),l&&w(t,n,(()=>p=0))},a=()=>{window.removeEventListener("ionKeyboardDidShow",a),window.addEventListener("ionKeyboardDidShow",i)};if(n){const e=await(0,r.g)(n),l=e.scrollHeight-e.clientHeight;if(c.scrollAmount>l-e.scrollTop)return"password"===t.type?(c.scrollAmount+=50,window.addEventListener("ionKeyboardDidShow",a)):window.addEventListener("ionKeyboardDidShow",i),void(o=setTimeout(i,1e3))}i()}},y=async(e,t)=>{const n=document,a="ios"===t,s="android"===t,c=e.getNumber("keyboardHeight",290),d=e.getBoolean("scrollAssist",!0),m=e.getBoolean("hideCaretOnScroll",a),v=e.getBoolean("inputBlurring",a),w=e.getBoolean("scrollPadding",!0),p=Array.from(n.querySelectorAll("ion-input, ion-textarea")),y=new WeakMap,h=new WeakMap,g=await l.getResizeMode(),E=async e=>{await new Promise((t=>(0,i.c)(e,t)));const t=e.shadowRoot||e,n=t.querySelector("input")||t.querySelector("textarea"),a=(0,r.a)(e),l=a?null:e.closest("ion-footer");if(n){if(a&&m&&!y.has(e)){const t=((e,t,n)=>{if(!n||!t)return()=>{};const o=n=>{var o;(o=t)===o.getRootNode().activeElement&&u(e,t,n)},r=()=>u(e,t,!1),a=()=>o(!0),l=()=>o(!1);return(0,i.a)(n,"ionScrollStart",a),(0,i.a)(n,"ionScrollEnd",l),t.addEventListener("blur",r),()=>{(0,i.r)(n,"ionScrollStart",a),(0,i.r)(n,"ionScrollEnd",l),t.removeEventListener("blur",r)}})(e,n,a);y.set(e,t)}if("date"!==n.type&&"datetime-local"!==n.type&&(a||l)&&d&&!h.has(e)){const t=((e,t,n,r,i,a,l,s=!1)=>{const u=a&&(void 0===l||l.mode===o.None),c=async()=>{b(e,t,n,r,i,u,s)};return e.addEventListener("focusin",c,!0),()=>{e.removeEventListener("focusin",c,!0)}})(e,n,a,l,c,w,g,s);h.set(e,t)}}};v&&(()=>{let e=!0,t=!1;const n=document;(0,i.a)(n,"ionScrollStart",(()=>{t=!0})),n.addEventListener("focusin",(()=>{e=!0}),!0),n.addEventListener("touchend",(o=>{if(t)return void(t=!1);const r=n.activeElement;if(!r)return;if(r.matches(f))return;const i=o.target;i!==r&&(i.matches(f)||i.closest(f)||(e=!1,setTimeout((()=>{e||r.blur()}),50)))}),!1)})();for(const e of p)E(e);n.addEventListener("ionInputDidLoad",(e=>{E(e.detail)})),n.addEventListener("ionInputDidUnload",(e=>{(e=>{if(m){const t=y.get(e);t&&t(),y.delete(e)}if(d){const t=h.get(e);t&&t(),h.delete(e)}})(e.detail)}))}}}]);