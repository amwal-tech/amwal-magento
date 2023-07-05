/*! For license information please see 2058.bundle.js.LICENSE.txt */
"use strict";(self.webpackChunkamwal_magento_react=self.webpackChunkamwal_magento_react||[]).push([[2058],{8248:function(t,r,e){e.d(r,{a:function(){return d},b:function(){return f},c:function(){return o},d:function(){return l},e:function(){return v},f:function(){return m},g:function(){return h},h:function(){return b},i:function(){return c},j:function(){return x},k:function(){return A},l:function(){return p},m:function(){return _},n:function(){return w},o:function(){return a},p:function(){return g},q:function(){return E},r:function(){return u},s:function(){return z},t:function(){return i},u:function(){return k},v:function(){return y}});const i=(t,r=0)=>new Promise((e=>{n(t,r,e)})),n=(t,r=0,e)=>{let i,n;const o={passive:!0},a=()=>{i&&i()},l=r=>{void 0!==r&&t!==r.target||(a(),e(r))};return t&&(t.addEventListener("webkitTransitionEnd",l,o),t.addEventListener("transitionend",l,o),n=setTimeout(l,r+500),i=()=>{n&&(clearTimeout(n),n=void 0),t.removeEventListener("webkitTransitionEnd",l,o),t.removeEventListener("transitionend",l,o)}),a},o=(t,r)=>{t.componentOnReady?t.componentOnReady().then((t=>r(t))):f((()=>r(t)))},a=t=>void 0!==t.componentOnReady,l=(t,r=[])=>{const e={};return r.forEach((r=>{t.hasAttribute(r)&&(null!==t.getAttribute(r)&&(e[r]=t.getAttribute(r)),t.removeAttribute(r))})),e},s=["role","aria-activedescendant","aria-atomic","aria-autocomplete","aria-braillelabel","aria-brailleroledescription","aria-busy","aria-checked","aria-colcount","aria-colindex","aria-colindextext","aria-colspan","aria-controls","aria-current","aria-describedby","aria-description","aria-details","aria-disabled","aria-errormessage","aria-expanded","aria-flowto","aria-haspopup","aria-hidden","aria-invalid","aria-keyshortcuts","aria-label","aria-labelledby","aria-level","aria-live","aria-multiline","aria-multiselectable","aria-orientation","aria-owns","aria-placeholder","aria-posinset","aria-pressed","aria-readonly","aria-relevant","aria-required","aria-roledescription","aria-rowcount","aria-rowindex","aria-rowindextext","aria-rowspan","aria-selected","aria-setsize","aria-sort","aria-valuemax","aria-valuemin","aria-valuenow","aria-valuetext"],c=(t,r)=>{let e=s;return r&&r.length>0&&(e=e.filter((t=>!r.includes(t)))),l(t,e)},d=(t,r,e,i)=>{var n;if("undefined"!=typeof window){const o=window,a=null===(n=null==o?void 0:o.Ionic)||void 0===n?void 0:n.config;if(a){const n=a.get("_ael");if(n)return n(t,r,e,i);if(a._ael)return a._ael(t,r,e,i)}}return t.addEventListener(r,e,i)},u=(t,r,e,i)=>{var n;if("undefined"!=typeof window){const o=window,a=null===(n=null==o?void 0:o.Ionic)||void 0===n?void 0:n.config;if(a){const n=a.get("_rel");if(n)return n(t,r,e,i);if(a._rel)return a._rel(t,r,e,i)}}return t.removeEventListener(r,e,i)},h=(t,r=t)=>t.shadowRoot||r,f=t=>"function"==typeof __zone_symbol__requestAnimationFrame?__zone_symbol__requestAnimationFrame(t):"function"==typeof requestAnimationFrame?requestAnimationFrame(t):setTimeout(t),b=t=>!!t.shadowRoot&&!!t.attachShadow,p=t=>{const r=t.closest("ion-item");return r?r.querySelector("ion-label"):null},m=t=>{if(t.focus(),t.classList.contains("ion-focusable")){const r=t.closest("ion-app");r&&r.setFocus([t])}},g=(t,r)=>{let e;const i=t.getAttribute("aria-labelledby"),n=t.id;let o=null!==i&&""!==i.trim()?i:r+"-lbl",a=null!==i&&""!==i.trim()?document.getElementById(i):p(t);return a?(null===i&&(a.id=o),e=a.textContent,a.setAttribute("aria-hidden","true")):""!==n.trim()&&(a=document.querySelector(`label[for="${n}"]`),a&&(""!==a.id?o=a.id:a.id=o=`${n}-lbl`,e=a.textContent)),{label:a,labelId:o,labelText:e}},v=(t,r,e,i,n)=>{if(t||b(r)){let t=r.querySelector("input.aux-input");t||(t=r.ownerDocument.createElement("input"),t.type="hidden",t.classList.add("aux-input"),r.appendChild(t)),t.disabled=n,t.name=e,t.value=i||""}},x=(t,r,e)=>Math.max(t,Math.min(r,e)),w=(t,r)=>{if(!t){const t="ASSERT: "+r;throw console.error(t),new Error(t)}},y=t=>t.timeStamp||Date.now(),k=t=>{if(t){const r=t.changedTouches;if(r&&r.length>0){const t=r[0];return{x:t.clientX,y:t.clientY}}if(void 0!==t.pageX)return{x:t.pageX,y:t.pageY}}return{x:0,y:0}},_=t=>{const r="rtl"===document.dir;switch(t){case"start":return r;case"end":return!r;default:throw new Error(`"${t}" is not a valid value for [side]. Use "start" or "end" instead.`)}},A=(t,r)=>{const e=t._original||t;return{_original:t,emit:E(e.emit.bind(e),r)}},E=(t,r=0)=>{let e;return(...i)=>{clearTimeout(e),e=setTimeout(t,r,...i)}},z=(t,r)=>{if(null!=t||(t={}),null!=r||(r={}),t===r)return!0;const e=Object.keys(t);if(e.length!==Object.keys(r).length)return!1;for(const i of e){if(!(i in r))return!1;if(t[i]!==r[i])return!1}return!0}},2058:function(t,r,e){e.r(r),e.d(r,{ion_card:function(){return l}});var i=e(2170),n=e(399),o=e(8248),a=e(601);const l=class{constructor(t){(0,i.r)(this,t),this.inheritedAriaAttributes={},this.color=void 0,this.button=!1,this.type="button",this.disabled=!1,this.download=void 0,this.href=void 0,this.rel=void 0,this.routerDirection="forward",this.routerAnimation=void 0,this.target=void 0}componentWillLoad(){this.inheritedAriaAttributes=(0,o.d)(this.el,["aria-label"])}isClickable(){return void 0!==this.href||this.button}renderCard(t){const r=this.isClickable();if(!r)return[(0,i.h)("slot",null)];const{href:e,routerAnimation:n,routerDirection:o,inheritedAriaAttributes:l}=this,s=r?void 0===e?"button":"a":"div",c="button"===s?{type:this.type}:{download:this.download,href:this.href,rel:this.rel,target:this.target};return(0,i.h)(s,Object.assign({},c,l,{class:"card-native",part:"native",disabled:this.disabled,onClick:t=>(0,a.o)(e,t,o,n)}),(0,i.h)("slot",null),r&&"md"===t&&(0,i.h)("ion-ripple-effect",null))}render(){const t=(0,n.g)(this);return(0,i.h)(i.H,{class:(0,a.c)(this.color,{[t]:!0,"card-disabled":this.disabled,"ion-activatable":this.isClickable()})},this.renderCard(t))}get el(){return(0,i.e)(this)}};l.style={ios:":host{--ion-safe-area-left:0px;--ion-safe-area-right:0px;-moz-osx-font-smoothing:grayscale;-webkit-font-smoothing:antialiased;display:block;position:relative;background:var(--background);color:var(--color);font-family:var(--ion-font-family, inherit);contain:content;overflow:hidden}:host(.ion-color){background:var(--ion-color-base);color:var(--ion-color-contrast)}:host(.card-disabled){cursor:default;opacity:0.3;pointer-events:none}.card-native{font-family:inherit;font-size:inherit;font-style:inherit;font-weight:inherit;letter-spacing:inherit;text-decoration:inherit;text-indent:inherit;text-overflow:inherit;text-transform:inherit;text-align:inherit;white-space:inherit;color:inherit;padding-left:0;padding-right:0;padding-top:0;padding-bottom:0;margin-left:0;margin-right:0;margin-top:0;margin-bottom:0;display:block;width:100%;min-height:var(--min-height);transition:var(--transition);border-width:var(--border-width);border-style:var(--border-style);border-color:var(--border-color);outline:none;background:inherit}.card-native::-moz-focus-inner{border:0}button,a{cursor:pointer;user-select:none;-webkit-user-drag:none}ion-ripple-effect{color:var(--ripple-color)}:host{--background:var(--ion-card-background, var(--ion-item-background, var(--ion-background-color, #fff)));--color:var(--ion-card-color, var(--ion-item-color, var(--ion-color-step-600, #666666)));-webkit-margin-start:16px;margin-inline-start:16px;-webkit-margin-end:16px;margin-inline-end:16px;margin-top:24px;margin-bottom:24px;border-radius:8px;transition:transform 500ms cubic-bezier(0.12, 0.72, 0.29, 1);font-size:14px;box-shadow:0 4px 16px rgba(0, 0, 0, 0.12)}:host(.ion-activated){transform:scale3d(0.97, 0.97, 1)}",md:":host{--ion-safe-area-left:0px;--ion-safe-area-right:0px;-moz-osx-font-smoothing:grayscale;-webkit-font-smoothing:antialiased;display:block;position:relative;background:var(--background);color:var(--color);font-family:var(--ion-font-family, inherit);contain:content;overflow:hidden}:host(.ion-color){background:var(--ion-color-base);color:var(--ion-color-contrast)}:host(.card-disabled){cursor:default;opacity:0.3;pointer-events:none}.card-native{font-family:inherit;font-size:inherit;font-style:inherit;font-weight:inherit;letter-spacing:inherit;text-decoration:inherit;text-indent:inherit;text-overflow:inherit;text-transform:inherit;text-align:inherit;white-space:inherit;color:inherit;padding-left:0;padding-right:0;padding-top:0;padding-bottom:0;margin-left:0;margin-right:0;margin-top:0;margin-bottom:0;display:block;width:100%;min-height:var(--min-height);transition:var(--transition);border-width:var(--border-width);border-style:var(--border-style);border-color:var(--border-color);outline:none;background:inherit}.card-native::-moz-focus-inner{border:0}button,a{cursor:pointer;user-select:none;-webkit-user-drag:none}ion-ripple-effect{color:var(--ripple-color)}:host{--background:var(--ion-card-background, var(--ion-item-background, var(--ion-background-color, #fff)));--color:var(--ion-card-color, var(--ion-item-color, var(--ion-color-step-550, #737373)));-webkit-margin-start:10px;margin-inline-start:10px;-webkit-margin-end:10px;margin-inline-end:10px;margin-top:10px;margin-bottom:10px;border-radius:4px;font-size:14px;box-shadow:0 3px 1px -2px rgba(0, 0, 0, 0.2), 0 2px 2px 0 rgba(0, 0, 0, 0.14), 0 1px 5px 0 rgba(0, 0, 0, 0.12)}"}},601:function(t,r,e){e.d(r,{c:function(){return n},g:function(){return o},h:function(){return i},o:function(){return l}});const i=(t,r)=>null!==r.closest(t),n=(t,r)=>"string"==typeof t&&t.length>0?Object.assign({"ion-color":!0,[`ion-color-${t}`]:!0},r):r,o=t=>{const r={};return(t=>void 0!==t?(Array.isArray(t)?t:t.split(" ")).filter((t=>null!=t)).map((t=>t.trim())).filter((t=>""!==t)):[])(t).forEach((t=>r[t]=!0)),r},a=/^[a-z][a-z0-9+\-.]*:/,l=async(t,r,e,i)=>{if(null!=t&&"#"!==t[0]&&!a.test(t)){const n=document.querySelector("ion-router");if(n)return null!=r&&r.preventDefault(),n.push(t,e,i)}return!1}}}]);