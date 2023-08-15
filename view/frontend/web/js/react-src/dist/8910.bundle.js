/*! For license information please see 8910.bundle.js.LICENSE.txt */
"use strict";(self.webpackChunkamwal_magento_react=self.webpackChunkamwal_magento_react||[]).push([[8910],{4026:function(e,t,n){n.d(t,{c:function(){return i}});var r=n(614);const i=e=>{const t=e;let n;return{hasLegacyControl:()=>{if(void 0===n){const e=void 0!==t.label||o(t),i=t.hasAttribute("aria-label")||t.hasAttribute("aria-labelledby")&&null===t.shadowRoot,a=(0,r.l)(t);n=!0===t.legacy||!e&&!i&&null!==a}return n}}},o=e=>!!(null!==e.shadowRoot&&(a.includes(e.tagName)&&null!==e.querySelector('[slot="label"]')||u.includes(e.tagName)&&""!==e.textContent)),a=["ION-RANGE"],u=["ION-TOGGLE","ION-CHECKBOX","ION-RADIO"]},614:function(e,t,n){n.d(t,{a:function(){return s},b:function(){return v},c:function(){return o},d:function(){return u},e:function(){return w},f:function(){return p},g:function(){return f},h:function(){return m},i:function(){return c},j:function(){return y},k:function(){return O},l:function(){return b},m:function(){return x},n:function(){return g},o:function(){return a},p:function(){return h},q:function(){return A},r:function(){return d},s:function(){return N},t:function(){return r},u:function(){return E},v:function(){return _}});const r=(e,t=0)=>new Promise((n=>{i(e,t,n)})),i=(e,t=0,n)=>{let r,i;const o={passive:!0},a=()=>{r&&r()},u=t=>{void 0!==t&&e!==t.target||(a(),n(t))};return e&&(e.addEventListener("webkitTransitionEnd",u,o),e.addEventListener("transitionend",u,o),i=setTimeout(u,t+500),r=()=>{i&&(clearTimeout(i),i=void 0),e.removeEventListener("webkitTransitionEnd",u,o),e.removeEventListener("transitionend",u,o)}),a},o=(e,t)=>{e.componentOnReady?e.componentOnReady().then((e=>t(e))):v((()=>t(e)))},a=e=>void 0!==e.componentOnReady,u=(e,t=[])=>{const n={};return t.forEach((t=>{e.hasAttribute(t)&&(null!==e.getAttribute(t)&&(n[t]=e.getAttribute(t)),e.removeAttribute(t))})),n},l=["role","aria-activedescendant","aria-atomic","aria-autocomplete","aria-braillelabel","aria-brailleroledescription","aria-busy","aria-checked","aria-colcount","aria-colindex","aria-colindextext","aria-colspan","aria-controls","aria-current","aria-describedby","aria-description","aria-details","aria-disabled","aria-errormessage","aria-expanded","aria-flowto","aria-haspopup","aria-hidden","aria-invalid","aria-keyshortcuts","aria-label","aria-labelledby","aria-level","aria-live","aria-multiline","aria-multiselectable","aria-orientation","aria-owns","aria-placeholder","aria-posinset","aria-pressed","aria-readonly","aria-relevant","aria-required","aria-roledescription","aria-rowcount","aria-rowindex","aria-rowindextext","aria-rowspan","aria-selected","aria-setsize","aria-sort","aria-valuemax","aria-valuemin","aria-valuenow","aria-valuetext"],c=(e,t)=>{let n=l;return t&&t.length>0&&(n=n.filter((e=>!t.includes(e)))),u(e,n)},s=(e,t,n,r)=>{var i;if("undefined"!=typeof window){const o=window,a=null===(i=null==o?void 0:o.Ionic)||void 0===i?void 0:i.config;if(a){const i=a.get("_ael");if(i)return i(e,t,n,r);if(a._ael)return a._ael(e,t,n,r)}}return e.addEventListener(t,n,r)},d=(e,t,n,r)=>{var i;if("undefined"!=typeof window){const o=window,a=null===(i=null==o?void 0:o.Ionic)||void 0===i?void 0:i.config;if(a){const i=a.get("_rel");if(i)return i(e,t,n,r);if(a._rel)return a._rel(e,t,n,r)}}return e.removeEventListener(t,n,r)},f=(e,t=e)=>e.shadowRoot||t,v=e=>"function"==typeof __zone_symbol__requestAnimationFrame?__zone_symbol__requestAnimationFrame(e):"function"==typeof requestAnimationFrame?requestAnimationFrame(e):setTimeout(e),m=e=>!!e.shadowRoot&&!!e.attachShadow,b=e=>{const t=e.closest("ion-item");return t?t.querySelector("ion-label"):null},p=e=>{if(e.focus(),e.classList.contains("ion-focusable")){const t=e.closest("ion-app");t&&t.setFocus([e])}},h=(e,t)=>{let n;const r=e.getAttribute("aria-labelledby"),i=e.id;let o=null!==r&&""!==r.trim()?r:t+"-lbl",a=null!==r&&""!==r.trim()?document.getElementById(r):b(e);return a?(null===r&&(a.id=o),n=a.textContent,a.setAttribute("aria-hidden","true")):""!==i.trim()&&(a=document.querySelector(`label[for="${i}"]`),a&&(""!==a.id?o=a.id:a.id=o=`${i}-lbl`,n=a.textContent)),{label:a,labelId:o,labelText:n}},w=(e,t,n,r,i)=>{if(e||m(t)){let e=t.querySelector("input.aux-input");e||(e=t.ownerDocument.createElement("input"),e.type="hidden",e.classList.add("aux-input"),t.appendChild(e)),e.disabled=i,e.name=n,e.value=r||""}},y=(e,t,n)=>Math.max(e,Math.min(t,n)),g=(e,t)=>{if(!e){const e="ASSERT: "+t;throw console.error(e),new Error(e)}},E=e=>e.timeStamp||Date.now(),_=e=>{if(e){const t=e.changedTouches;if(t&&t.length>0){const e=t[0];return{x:e.clientX,y:e.clientY}}if(void 0!==e.pageX)return{x:e.pageX,y:e.pageY}}return{x:0,y:0}},x=e=>{const t="rtl"===document.dir;switch(e){case"start":return t;case"end":return!t;default:throw new Error(`"${e}" is not a valid value for [side]. Use "start" or "end" instead.`)}},O=(e,t)=>{const n=e._original||e;return{_original:e,emit:A(n.emit.bind(n),t)}},A=(e,t=0)=>{let n;return(...r)=>{clearTimeout(n),n=setTimeout(e,t,...r)}},N=(e,t)=>{if(null!=e||(e={}),null!=t||(t={}),e===t)return!0;const n=Object.keys(e);if(n.length!==Object.keys(t).length)return!1;for(const r of n){if(!(r in t))return!1;if(e[r]!==t[r])return!1}return!0}},1983:function(e,t,n){n.d(t,{a:function(){return i},b:function(){return o},p:function(){return r}});const r=(e,...t)=>console.warn(`[Ionic Warning]: ${e}`,...t),i=(e,...t)=>console.error(`[Ionic Error]: ${e}`,...t),o=(e,...t)=>console.error(`<${e.tagName.toLowerCase()}> must be used inside ${t.join(" or ")}.`)},261:function(e,t,n){n.d(t,{d:function(){return i},w:function(){return r}});const r="undefined"!=typeof window?window:void 0,i="undefined"!=typeof document?document:void 0},3021:function(e,t,n){n.d(t,{c:function(){return a},g:function(){return u}});var r=n(261),i=n(614),o=n(1983);const a=(e,t,n)=>{let o,a;void 0!==r.w&&"MutationObserver"in r.w&&(o=new MutationObserver((e=>{for(const r of e)for(const e of r.addedNodes)if(e.nodeType===Node.ELEMENT_NODE&&e.slot===t)return n(),void(0,i.b)((()=>u(e)))})),o.observe(e,{childList:!0}));const u=e=>{var r;a&&(a.disconnect(),a=void 0),a=new MutationObserver((e=>{n();for(const n of e)for(const e of n.removedNodes)e.nodeType===Node.ELEMENT_NODE&&e.slot===t&&l()})),a.observe(null!==(r=e.parentElement)&&void 0!==r?r:e,{subtree:!0,childList:!0})},l=()=>{a&&(a.disconnect(),a=void 0)};return{destroy:()=>{o&&(o.disconnect(),o=void 0),l()}}},u=(e,t,n)=>{const r=null==e?0:e.toString().length,i=l(r,t);if(void 0===n)return i;try{return n(r,t)}catch(e){return(0,o.a)("Exception in provided `counterFormatter`.",e),i}},l=(e,t)=>`${e} / ${t}`},7141:function(e,t,n){n.d(t,{c:function(){return o}});var r=n(261),i=n(614);const o=(e,t,n)=>{let o;const a=()=>void 0!==t()&&void 0===e.label&&null!==n(),u=()=>{const i=t();if(void 0===i)return;if(!a())return void i.style.removeProperty("width");const l=n().scrollWidth;if(0===l&&null===i.offsetParent&&void 0!==r.w&&"IntersectionObserver"in r.w){if(void 0!==o)return;const t=o=new IntersectionObserver((e=>{1===e[0].intersectionRatio&&(u(),t.disconnect(),o=void 0)}),{threshold:.01,root:e});t.observe(i)}else i.style.setProperty("width",.75*l+"px")};return{calculateNotchWidth:()=>{a()&&(0,i.b)((()=>{u()}))},destroy:()=>{o&&(o.disconnect(),o=void 0)}}}},601:function(e,t,n){n.d(t,{c:function(){return i},g:function(){return o},h:function(){return r},o:function(){return u}});const r=(e,t)=>null!==t.closest(e),i=(e,t)=>"string"==typeof e&&e.length>0?Object.assign({"ion-color":!0,[`ion-color-${e}`]:!0},t):t,o=e=>{const t={};return(e=>void 0!==e?(Array.isArray(e)?e:e.split(" ")).filter((e=>null!=e)).map((e=>e.trim())).filter((e=>""!==e)):[])(e).forEach((e=>t[e]=!0)),t},a=/^[a-z][a-z0-9+\-.]*:/,u=async(e,t,n,r)=>{if(null!=e&&"#"!==e[0]&&!a.test(e)){const i=document.querySelector("ion-router");if(i)return null!=t&&t.preventDefault(),i.push(e,n,r)}return!1}}}]);