/*! For license information please see 9972.bundle.js.LICENSE.txt */
"use strict";(self.webpackChunkamwal_magento_react=self.webpackChunkamwal_magento_react||[]).push([[9972],{5659:function(e,t,n){n.d(t,{C:function(){return o},a:function(){return i},d:function(){return a}});var r=n(614);const i=async(e,t,n,i,a,o)=>{var l;if(e)return e.attachViewToDom(t,n,a,i);if(!(o||"string"==typeof n||n instanceof HTMLElement))throw new Error("framework delegate is missing");const s="string"==typeof n?null===(l=t.ownerDocument)||void 0===l?void 0:l.createElement(n):n;return i&&i.forEach((e=>s.classList.add(e))),a&&Object.assign(s,a),t.appendChild(s),await new Promise((e=>(0,r.c)(s,e))),s},a=(e,t)=>{if(t){if(e){const n=t.parentElement;return e.removeViewFromDom(n,t)}t.remove()}return Promise.resolve()},o=()=>{let e,t;return{attachViewToDom:async(n,i,a={},o=[])=>{var l,s;let c;if(e=n,i){const t="string"==typeof i?null===(l=e.ownerDocument)||void 0===l?void 0:l.createElement(i):i;o.forEach((e=>t.classList.add(e))),Object.assign(t,a),e.appendChild(t),c=t,await new Promise((e=>(0,r.c)(t,e)))}else if(e.children.length>0&&("ION-MODAL"===e.tagName||"ION-POPOVER"===e.tagName)&&!(c=e.children[0]).classList.contains("ion-delegate-host")){const t=null===(s=e.ownerDocument)||void 0===s?void 0:s.createElement("div");t.classList.add("ion-delegate-host"),o.forEach((e=>t.classList.add(e))),t.append(...e.children),e.appendChild(t),c=t}const u=document.querySelector("ion-app")||document.body;return t=document.createComment("ionic teleport"),e.parentNode.insertBefore(t,e),u.appendChild(e),null!=c?c:e},removeViewFromDom:()=>(e&&t&&(t.parentNode.insertBefore(e,t),t.remove()),Promise.resolve())}}},614:function(e,t,n){n.d(t,{a:function(){return u},b:function(){return f},c:function(){return a},d:function(){return l},e:function(){return w},f:function(){return v},g:function(){return m},h:function(){return h},i:function(){return c},j:function(){return g},k:function(){return x},l:function(){return p},m:function(){return L},n:function(){return y},o:function(){return o},p:function(){return b},q:function(){return A},r:function(){return d},s:function(){return T},t:function(){return r},u:function(){return E},v:function(){return _}});const r=(e,t=0)=>new Promise((n=>{i(e,t,n)})),i=(e,t=0,n)=>{let r,i;const a={passive:!0},o=()=>{r&&r()},l=t=>{void 0!==t&&e!==t.target||(o(),n(t))};return e&&(e.addEventListener("webkitTransitionEnd",l,a),e.addEventListener("transitionend",l,a),i=setTimeout(l,t+500),r=()=>{i&&(clearTimeout(i),i=void 0),e.removeEventListener("webkitTransitionEnd",l,a),e.removeEventListener("transitionend",l,a)}),o},a=(e,t)=>{e.componentOnReady?e.componentOnReady().then((e=>t(e))):f((()=>t(e)))},o=e=>void 0!==e.componentOnReady,l=(e,t=[])=>{const n={};return t.forEach((t=>{e.hasAttribute(t)&&(null!==e.getAttribute(t)&&(n[t]=e.getAttribute(t)),e.removeAttribute(t))})),n},s=["role","aria-activedescendant","aria-atomic","aria-autocomplete","aria-braillelabel","aria-brailleroledescription","aria-busy","aria-checked","aria-colcount","aria-colindex","aria-colindextext","aria-colspan","aria-controls","aria-current","aria-describedby","aria-description","aria-details","aria-disabled","aria-errormessage","aria-expanded","aria-flowto","aria-haspopup","aria-hidden","aria-invalid","aria-keyshortcuts","aria-label","aria-labelledby","aria-level","aria-live","aria-multiline","aria-multiselectable","aria-orientation","aria-owns","aria-placeholder","aria-posinset","aria-pressed","aria-readonly","aria-relevant","aria-required","aria-roledescription","aria-rowcount","aria-rowindex","aria-rowindextext","aria-rowspan","aria-selected","aria-setsize","aria-sort","aria-valuemax","aria-valuemin","aria-valuenow","aria-valuetext"],c=(e,t)=>{let n=s;return t&&t.length>0&&(n=n.filter((e=>!t.includes(e)))),l(e,n)},u=(e,t,n,r)=>{var i;if("undefined"!=typeof window){const a=window,o=null===(i=null==a?void 0:a.Ionic)||void 0===i?void 0:i.config;if(o){const i=o.get("_ael");if(i)return i(e,t,n,r);if(o._ael)return o._ael(e,t,n,r)}}return e.addEventListener(t,n,r)},d=(e,t,n,r)=>{var i;if("undefined"!=typeof window){const a=window,o=null===(i=null==a?void 0:a.Ionic)||void 0===i?void 0:i.config;if(o){const i=o.get("_rel");if(i)return i(e,t,n,r);if(o._rel)return o._rel(e,t,n,r)}}return e.removeEventListener(t,n,r)},m=(e,t=e)=>e.shadowRoot||t,f=e=>"function"==typeof __zone_symbol__requestAnimationFrame?__zone_symbol__requestAnimationFrame(e):"function"==typeof requestAnimationFrame?requestAnimationFrame(e):setTimeout(e),h=e=>!!e.shadowRoot&&!!e.attachShadow,p=e=>{const t=e.closest("ion-item");return t?t.querySelector("ion-label"):null},v=e=>{if(e.focus(),e.classList.contains("ion-focusable")){const t=e.closest("ion-app");t&&t.setFocus([e])}},b=(e,t)=>{let n;const r=e.getAttribute("aria-labelledby"),i=e.id;let a=null!==r&&""!==r.trim()?r:t+"-lbl",o=null!==r&&""!==r.trim()?document.getElementById(r):p(e);return o?(null===r&&(o.id=a),n=o.textContent,o.setAttribute("aria-hidden","true")):""!==i.trim()&&(o=document.querySelector(`label[for="${i}"]`),o&&(""!==o.id?a=o.id:o.id=a=`${i}-lbl`,n=o.textContent)),{label:o,labelId:a,labelText:n}},w=(e,t,n,r,i)=>{if(e||h(t)){let e=t.querySelector("input.aux-input");e||(e=t.ownerDocument.createElement("input"),e.type="hidden",e.classList.add("aux-input"),t.appendChild(e)),e.disabled=i,e.name=n,e.value=r||""}},g=(e,t,n)=>Math.max(e,Math.min(t,n)),y=(e,t)=>{if(!e){const e="ASSERT: "+t;throw console.error(e),new Error(e)}},E=e=>e.timeStamp||Date.now(),_=e=>{if(e){const t=e.changedTouches;if(t&&t.length>0){const e=t[0];return{x:e.clientX,y:e.clientY}}if(void 0!==e.pageX)return{x:e.pageX,y:e.pageY}}return{x:0,y:0}},L=e=>{const t="rtl"===document.dir;switch(e){case"start":return t;case"end":return!t;default:throw new Error(`"${e}" is not a valid value for [side]. Use "start" or "end" instead.`)}},x=(e,t)=>{const n=e._original||e;return{_original:e,emit:A(n.emit.bind(n),t)}},A=(e,t=0)=>{let n;return(...r)=>{clearTimeout(n),n=setTimeout(e,t,...r)}},T=(e,t)=>{if(null!=e||(e={}),null!=t||(t={}),e===t)return!0;const n=Object.keys(e);if(n.length!==Object.keys(t).length)return!1;for(const r of n){if(!(r in t))return!1;if(e[r]!==t[r])return!1}return!0}},9972:function(e,t,n){n.r(t),n.d(t,{ion_tab:function(){return a}});var r=n(3542),i=n(5659);const a=class{constructor(e){(0,r.r)(this,e),this.loaded=!1,this.active=!1,this.delegate=void 0,this.tab=void 0,this.component=void 0}async componentWillLoad(){this.active&&await this.setActive()}async setActive(){await this.prepareLazyLoaded(),this.active=!0}changeActive(e){e&&this.prepareLazyLoaded()}prepareLazyLoaded(){if(!this.loaded&&null!=this.component){this.loaded=!0;try{return(0,i.a)(this.delegate,this.el,this.component,["ion-page"])}catch(e){console.error(e)}}return Promise.resolve(void 0)}render(){const{tab:e,active:t,component:n}=this;return(0,r.h)(r.H,{role:"tabpanel","aria-hidden":t?null:"true","aria-labelledby":`tab-button-${e}`,class:{"ion-page":void 0===n,"tab-hidden":!t}},(0,r.h)("slot",null))}get el(){return(0,r.e)(this)}static get watchers(){return{active:["changeActive"]}}};a.style=":host(.tab-hidden){display:none !important}"}}]);