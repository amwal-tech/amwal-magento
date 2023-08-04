/*! For license information please see 4958.bundle.js.LICENSE.txt */
"use strict";(self.webpackChunkamwal_magento_react=self.webpackChunkamwal_magento_react||[]).push([[4958],{8248:function(o,t,n){n.d(t,{a:function(){return d},b:function(){return g},c:function(){return a},d:function(){return s},e:function(){return m},f:function(){return f},g:function(){return v},h:function(){return h},i:function(){return l},j:function(){return w},k:function(){return M},l:function(){return b},m:function(){return z},n:function(){return x},o:function(){return e},p:function(){return p},q:function(){return B},r:function(){return u},s:function(){return _},t:function(){return r},u:function(){return y},v:function(){return k}});const r=(o,t=0)=>new Promise((n=>{i(o,t,n)})),i=(o,t=0,n)=>{let r,i;const a={passive:!0},e=()=>{r&&r()},s=t=>{void 0!==t&&o!==t.target||(e(),n(t))};return o&&(o.addEventListener("webkitTransitionEnd",s,a),o.addEventListener("transitionend",s,a),i=setTimeout(s,t+500),r=()=>{i&&(clearTimeout(i),i=void 0),o.removeEventListener("webkitTransitionEnd",s,a),o.removeEventListener("transitionend",s,a)}),e},a=(o,t)=>{o.componentOnReady?o.componentOnReady().then((o=>t(o))):g((()=>t(o)))},e=o=>void 0!==o.componentOnReady,s=(o,t=[])=>{const n={};return t.forEach((t=>{o.hasAttribute(t)&&(null!==o.getAttribute(t)&&(n[t]=o.getAttribute(t)),o.removeAttribute(t))})),n},c=["role","aria-activedescendant","aria-atomic","aria-autocomplete","aria-braillelabel","aria-brailleroledescription","aria-busy","aria-checked","aria-colcount","aria-colindex","aria-colindextext","aria-colspan","aria-controls","aria-current","aria-describedby","aria-description","aria-details","aria-disabled","aria-errormessage","aria-expanded","aria-flowto","aria-haspopup","aria-hidden","aria-invalid","aria-keyshortcuts","aria-label","aria-labelledby","aria-level","aria-live","aria-multiline","aria-multiselectable","aria-orientation","aria-owns","aria-placeholder","aria-posinset","aria-pressed","aria-readonly","aria-relevant","aria-required","aria-roledescription","aria-rowcount","aria-rowindex","aria-rowindextext","aria-rowspan","aria-selected","aria-setsize","aria-sort","aria-valuemax","aria-valuemin","aria-valuenow","aria-valuetext"],l=(o,t)=>{let n=c;return t&&t.length>0&&(n=n.filter((o=>!t.includes(o)))),s(o,n)},d=(o,t,n,r)=>{var i;if("undefined"!=typeof window){const a=window,e=null===(i=null==a?void 0:a.Ionic)||void 0===i?void 0:i.config;if(e){const i=e.get("_ael");if(i)return i(o,t,n,r);if(e._ael)return e._ael(o,t,n,r)}}return o.addEventListener(t,n,r)},u=(o,t,n,r)=>{var i;if("undefined"!=typeof window){const a=window,e=null===(i=null==a?void 0:a.Ionic)||void 0===i?void 0:i.config;if(e){const i=e.get("_rel");if(i)return i(o,t,n,r);if(e._rel)return e._rel(o,t,n,r)}}return o.removeEventListener(t,n,r)},v=(o,t=o)=>o.shadowRoot||t,g=o=>"function"==typeof __zone_symbol__requestAnimationFrame?__zone_symbol__requestAnimationFrame(o):"function"==typeof requestAnimationFrame?requestAnimationFrame(o):setTimeout(o),h=o=>!!o.shadowRoot&&!!o.attachShadow,b=o=>{const t=o.closest("ion-item");return t?t.querySelector("ion-label"):null},f=o=>{if(o.focus(),o.classList.contains("ion-focusable")){const t=o.closest("ion-app");t&&t.setFocus([o])}},p=(o,t)=>{let n;const r=o.getAttribute("aria-labelledby"),i=o.id;let a=null!==r&&""!==r.trim()?r:t+"-lbl",e=null!==r&&""!==r.trim()?document.getElementById(r):b(o);return e?(null===r&&(e.id=a),n=e.textContent,e.setAttribute("aria-hidden","true")):""!==i.trim()&&(e=document.querySelector(`label[for="${i}"]`),e&&(""!==e.id?a=e.id:e.id=a=`${i}-lbl`,n=e.textContent)),{label:e,labelId:a,labelText:n}},m=(o,t,n,r,i)=>{if(o||h(t)){let o=t.querySelector("input.aux-input");o||(o=t.ownerDocument.createElement("input"),o.type="hidden",o.classList.add("aux-input"),t.appendChild(o)),o.disabled=i,o.name=n,o.value=r||""}},w=(o,t,n)=>Math.max(o,Math.min(t,n)),x=(o,t)=>{if(!o){const o="ASSERT: "+t;throw console.error(o),new Error(o)}},k=o=>o.timeStamp||Date.now(),y=o=>{if(o){const t=o.changedTouches;if(t&&t.length>0){const o=t[0];return{x:o.clientX,y:o.clientY}}if(void 0!==o.pageX)return{x:o.pageX,y:o.pageY}}return{x:0,y:0}},z=o=>{const t="rtl"===document.dir;switch(o){case"start":return t;case"end":return!t;default:throw new Error(`"${o}" is not a valid value for [side]. Use "start" or "end" instead.`)}},M=(o,t)=>{const n=o._original||o;return{_original:o,emit:B(n.emit.bind(n),t)}},B=(o,t=0)=>{let n;return(...r)=>{clearTimeout(n),n=setTimeout(o,t,...r)}},_=(o,t)=>{if(null!=o||(o={}),null!=t||(t={}),o===t)return!0;const n=Object.keys(o);if(n.length!==Object.keys(t).length)return!1;for(const r of n){if(!(r in t))return!1;if(o[r]!==t[r])return!1}return!0}},2839:function(o,t,n){n.d(t,{a:function(){return l},b:function(){return r},c:function(){return d},d:function(){return g},e:function(){return m},f:function(){return v},g:function(){return s},h:function(){return e},i:function(){return h},j:function(){return b},k:function(){return f},l:function(){return x},m:function(){return w},n:function(){return a},o:function(){return i},p:function(){return z},q:function(){return B},r:function(){return y},s:function(){return M},t:function(){return u},u:function(){return c},v:function(){return k},w:function(){return p}});const r="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path stroke-linecap='square' stroke-miterlimit='10' stroke-width='48' d='M244 400L100 256l144-144M120 256h292' class='ionicon-fill-none'/></svg>",i="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='48' d='M112 268l144 144 144-144M256 392V100' class='ionicon-fill-none'/></svg>",a="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path d='M368 64L144 256l224 192V64z'/></svg>",e="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path d='M64 144l192 224 192-224H64z'/></svg>",s="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path d='M448 368L256 144 64 368h384z'/></svg>",c="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path stroke-linecap='round' stroke-linejoin='round' d='M416 128L192 384l-96-96' class='ionicon-fill-none ionicon-stroke-width'/></svg>",l="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='48' d='M328 112L184 256l144 144' class='ionicon-fill-none'/></svg>",d="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='48' d='M112 184l144 144 144-144' class='ionicon-fill-none'/></svg>",u="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path d='M136 208l120-104 120 104M136 304l120 104 120-104' stroke-width='48' stroke-linecap='round' stroke-linejoin='round' class='ionicon-fill-none'/></svg>",v="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='48' d='M184 112l144 144-144 144' class='ionicon-fill-none'/></svg>",g="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='48' d='M184 112l144 144-144 144' class='ionicon-fill-none'/></svg>",h="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path d='M289.94 256l95-95A24 24 0 00351 127l-95 95-95-95a24 24 0 00-34 34l95 95-95 95a24 24 0 1034 34l95-95 95 95a24 24 0 0034-34z'/></svg>",b="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path d='M256 48C141.31 48 48 141.31 48 256s93.31 208 208 208 208-93.31 208-208S370.69 48 256 48zm75.31 260.69a16 16 0 11-22.62 22.62L256 278.63l-52.69 52.68a16 16 0 01-22.62-22.62L233.37 256l-52.68-52.69a16 16 0 0122.62-22.62L256 233.37l52.69-52.68a16 16 0 0122.62 22.62L278.63 256z'/></svg>",f="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path d='M400 145.49L366.51 112 256 222.51 145.49 112 112 145.49 222.51 256 112 366.51 145.49 400 256 289.49 366.51 400 400 366.51 289.49 256 400 145.49z'/></svg>",p="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><circle cx='256' cy='256' r='192' stroke-linecap='round' stroke-linejoin='round' class='ionicon-fill-none ionicon-stroke-width'/></svg>",m="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><circle cx='256' cy='256' r='48'/><circle cx='416' cy='256' r='48'/><circle cx='96' cy='256' r='48'/></svg>",w="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path stroke-linecap='round' stroke-miterlimit='10' d='M80 160h352M80 256h352M80 352h352' class='ionicon-fill-none ionicon-stroke-width'/></svg>",x="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path d='M64 384h384v-42.67H64zm0-106.67h384v-42.66H64zM64 128v42.67h384V128z'/></svg>",k="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path stroke-linecap='round' stroke-linejoin='round' d='M400 256H112' class='ionicon-fill-none ionicon-stroke-width'/></svg>",y="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path stroke-linecap='round' stroke-linejoin='round' d='M96 256h320M96 176h320M96 336h320' class='ionicon-fill-none ionicon-stroke-width'/></svg>",z="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path stroke-linecap='square' stroke-linejoin='round' stroke-width='44' d='M118 304h276M118 208h276' class='ionicon-fill-none'/></svg>",M="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path d='M221.09 64a157.09 157.09 0 10157.09 157.09A157.1 157.1 0 00221.09 64z' stroke-miterlimit='10' class='ionicon-fill-none ionicon-stroke-width'/><path stroke-linecap='round' stroke-miterlimit='10' d='M338.29 338.29L448 448' class='ionicon-fill-none ionicon-stroke-width'/></svg>",B="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path d='M464 428L339.92 303.9a160.48 160.48 0 0030.72-94.58C370.64 120.37 298.27 48 209.32 48S48 120.37 48 209.32s72.37 161.32 161.32 161.32a160.48 160.48 0 0094.58-30.72L428 464zM209.32 319.69a110.38 110.38 0 11110.37-110.37 110.5 110.5 0 01-110.37 110.37z'/></svg>"},4958:function(o,t,n){n.r(t),n.d(t,{ion_fab_button:function(){return c}});var r=n(2170),i=n(2839),a=n(399),e=n(8248),s=n(601);const c=class{constructor(o){(0,r.r)(this,o),this.ionFocus=(0,r.d)(this,"ionFocus",7),this.ionBlur=(0,r.d)(this,"ionBlur",7),this.fab=null,this.inheritedAttributes={},this.onFocus=()=>{this.ionFocus.emit()},this.onBlur=()=>{this.ionBlur.emit()},this.onClick=()=>{const{fab:o}=this;o&&o.toggle()},this.color=void 0,this.activated=!1,this.disabled=!1,this.download=void 0,this.href=void 0,this.rel=void 0,this.routerDirection="forward",this.routerAnimation=void 0,this.target=void 0,this.show=!1,this.translucent=!1,this.type="button",this.size=void 0,this.closeIcon=i.i}connectedCallback(){this.fab=this.el.closest("ion-fab")}componentWillLoad(){this.inheritedAttributes=(0,e.i)(this.el)}render(){const{el:o,disabled:t,color:n,href:i,activated:e,show:c,translucent:l,size:d,inheritedAttributes:u}=this,v=(0,s.h)("ion-fab-list",o),g=(0,a.g)(this),h=void 0===i?"button":"a",b="button"===h?{type:this.type}:{download:this.download,href:i,rel:this.rel,target:this.target};return(0,r.h)(r.H,{onClick:this.onClick,"aria-disabled":t?"true":null,class:(0,s.c)(n,{[g]:!0,"fab-button-in-list":v,"fab-button-translucent-in-list":v&&l,"fab-button-close-active":e,"fab-button-show":c,"fab-button-disabled":t,"fab-button-translucent":l,"ion-activatable":!0,"ion-focusable":!0,[`fab-button-${d}`]:void 0!==d})},(0,r.h)(h,Object.assign({},b,{class:"button-native",part:"native",disabled:t,onFocus:this.onFocus,onBlur:this.onBlur,onClick:o=>(0,s.o)(i,o,this.routerDirection,this.routerAnimation)},u),(0,r.h)("ion-icon",{"aria-hidden":"true",icon:this.closeIcon,part:"close-icon",class:"close-icon",lazy:!1}),(0,r.h)("span",{class:"button-inner"},(0,r.h)("slot",null)),"md"===g&&(0,r.h)("ion-ripple-effect",null)))}get el(){return(0,r.e)(this)}};c.style={ios:':host{--color-activated:var(--color);--color-focused:var(--color);--color-hover:var(--color);--background-hover:var(--ion-color-primary-contrast, #fff);--background-hover-opacity:.08;--transition:background-color, opacity 100ms linear;--ripple-color:currentColor;--border-radius:50%;--border-width:0;--border-style:none;--border-color:initial;--padding-top:0;--padding-end:0;--padding-bottom:0;--padding-start:0;margin-left:0;margin-right:0;margin-top:0;margin-bottom:0;display:block;width:56px;height:56px;font-size:14px;text-align:center;text-overflow:ellipsis;text-transform:none;white-space:nowrap;font-kerning:none}.button-native{border-radius:var(--border-radius);-webkit-padding-start:var(--padding-start);padding-inline-start:var(--padding-start);-webkit-padding-end:var(--padding-end);padding-inline-end:var(--padding-end);padding-top:var(--padding-top);padding-bottom:var(--padding-bottom);font-family:inherit;font-size:inherit;font-style:inherit;font-weight:inherit;letter-spacing:inherit;text-decoration:inherit;text-indent:inherit;text-overflow:inherit;text-transform:inherit;text-align:inherit;white-space:inherit;color:inherit;display:block;position:relative;width:100%;height:100%;transform:var(--transform);transition:var(--transition);border-width:var(--border-width);border-style:var(--border-style);border-color:var(--border-color);outline:none;background:var(--background);background-clip:padding-box;color:var(--color);box-shadow:var(--box-shadow);contain:strict;cursor:pointer;overflow:hidden;z-index:0;appearance:none;box-sizing:border-box}::slotted(ion-icon){line-height:1}.button-native::after{left:0;right:0;top:0;bottom:0;position:absolute;content:"";opacity:0}.button-inner{left:0;right:0;top:0;display:flex;position:absolute;flex-flow:row nowrap;flex-shrink:0;align-items:center;justify-content:center;height:100%;transition:all ease-in-out 300ms;transition-property:transform, opacity;z-index:1}:host(.fab-button-disabled){cursor:default;opacity:0.5;pointer-events:none}@media (any-hover: hover){:host(:hover) .button-native{color:var(--color-hover)}:host(:hover) .button-native::after{background:var(--background-hover);opacity:var(--background-hover-opacity)}}:host(.ion-focused) .button-native{color:var(--color-focused)}:host(.ion-focused) .button-native::after{background:var(--background-focused);opacity:var(--background-focused-opacity)}:host(.ion-activated) .button-native{color:var(--color-activated)}:host(.ion-activated) .button-native::after{background:var(--background-activated);opacity:var(--background-activated-opacity)}::slotted(ion-icon){line-height:1}:host(.fab-button-small){-webkit-margin-start:8px;margin-inline-start:8px;-webkit-margin-end:8px;margin-inline-end:8px;margin-top:8px;margin-bottom:8px;width:40px;height:40px}.close-icon{-webkit-margin-start:auto;margin-inline-start:auto;-webkit-margin-end:auto;margin-inline-end:auto;margin-top:0;margin-bottom:0;left:0;right:0;top:0;position:absolute;height:100%;transform:scale(0.4) rotateZ(-45deg);transition:all ease-in-out 300ms;transition-property:transform, opacity;font-size:var(--close-icon-font-size);opacity:0;z-index:1}:host(.fab-button-close-active) .close-icon{transform:scale(1) rotateZ(0deg);opacity:1}:host(.fab-button-close-active) .button-inner{transform:scale(0.4) rotateZ(45deg);opacity:0}ion-ripple-effect{color:var(--ripple-color)}@supports (backdrop-filter: blur(0)){:host(.fab-button-translucent) .button-native{backdrop-filter:var(--backdrop-filter)}}:host(.ion-color) .button-native{background:var(--ion-color-base);color:var(--ion-color-contrast)}:host{--background:var(--ion-color-primary, #3880ff);--background-activated:var(--ion-color-primary-shade, #3171e0);--background-focused:var(--ion-color-primary-shade, #3171e0);--background-hover:var(--ion-color-primary-tint, #4c8dff);--background-activated-opacity:1;--background-focused-opacity:1;--background-hover-opacity:1;--color:var(--ion-color-primary-contrast, #fff);--box-shadow:0 4px 16px rgba(0, 0, 0, 0.12);--transition:0.2s transform cubic-bezier(0.25, 1.11, 0.78, 1.59);--close-icon-font-size:28px}:host(.ion-activated){--box-shadow:0 4px 16px rgba(0, 0, 0, 0.12);--transform:scale(1.1);--transition:0.2s transform ease-out}::slotted(ion-icon){font-size:28px}:host(.fab-button-in-list){--background:var(--ion-color-light, #f4f5f8);--background-activated:var(--ion-color-light-shade, #d7d8da);--background-focused:var(--background-activated);--background-hover:var(--ion-color-light-tint, #f5f6f9);--color:var(--ion-color-light-contrast, #000);--color-activated:var(--ion-color-light-contrast, #000);--color-focused:var(--color-activated);--transition:transform 200ms ease 10ms, opacity 200ms ease 10ms}:host(.fab-button-in-list) ::slotted(ion-icon){font-size:18px}:host(.ion-color.ion-focused) .button-native::after{background:var(--ion-color-shade)}:host(.ion-color.ion-focused) .button-native,:host(.ion-color.ion-activated) .button-native{color:var(--ion-color-contrast)}:host(.ion-color.ion-focused) .button-native::after,:host(.ion-color.ion-activated) .button-native::after{background:var(--ion-color-shade)}@media (any-hover: hover){:host(.ion-color:hover) .button-native{color:var(--ion-color-contrast)}:host(.ion-color:hover) .button-native::after{background:var(--ion-color-tint)}}@supports (backdrop-filter: blur(0)){:host(.fab-button-translucent){--background:rgba(var(--ion-color-primary-rgb, 56, 128, 255), 0.9);--background-hover:rgba(var(--ion-color-primary-rgb, 56, 128, 255), 0.8);--background-focused:rgba(var(--ion-color-primary-rgb, 56, 128, 255), 0.82);--backdrop-filter:saturate(180%) blur(20px)}:host(.fab-button-translucent-in-list){--background:rgba(var(--ion-color-light-rgb, 244, 245, 248), 0.9);--background-hover:rgba(var(--ion-color-light-rgb, 244, 245, 248), 0.8);--background-focused:rgba(var(--ion-color-light-rgb, 244, 245, 248), 0.82)}}@supports (backdrop-filter: blur(0)){@media (any-hover: hover){:host(.fab-button-translucent.ion-color:hover) .button-native{background:rgba(var(--ion-color-base-rgb), 0.8)}}:host(.ion-color.fab-button-translucent) .button-native{background:rgba(var(--ion-color-base-rgb), 0.9)}:host(.ion-color.ion-focused.fab-button-translucent) .button-native,:host(.ion-color.ion-activated.fab-button-translucent) .button-native{background:var(--ion-color-base)}}',md:':host{--color-activated:var(--color);--color-focused:var(--color);--color-hover:var(--color);--background-hover:var(--ion-color-primary-contrast, #fff);--background-hover-opacity:.08;--transition:background-color, opacity 100ms linear;--ripple-color:currentColor;--border-radius:50%;--border-width:0;--border-style:none;--border-color:initial;--padding-top:0;--padding-end:0;--padding-bottom:0;--padding-start:0;margin-left:0;margin-right:0;margin-top:0;margin-bottom:0;display:block;width:56px;height:56px;font-size:14px;text-align:center;text-overflow:ellipsis;text-transform:none;white-space:nowrap;font-kerning:none}.button-native{border-radius:var(--border-radius);-webkit-padding-start:var(--padding-start);padding-inline-start:var(--padding-start);-webkit-padding-end:var(--padding-end);padding-inline-end:var(--padding-end);padding-top:var(--padding-top);padding-bottom:var(--padding-bottom);font-family:inherit;font-size:inherit;font-style:inherit;font-weight:inherit;letter-spacing:inherit;text-decoration:inherit;text-indent:inherit;text-overflow:inherit;text-transform:inherit;text-align:inherit;white-space:inherit;color:inherit;display:block;position:relative;width:100%;height:100%;transform:var(--transform);transition:var(--transition);border-width:var(--border-width);border-style:var(--border-style);border-color:var(--border-color);outline:none;background:var(--background);background-clip:padding-box;color:var(--color);box-shadow:var(--box-shadow);contain:strict;cursor:pointer;overflow:hidden;z-index:0;appearance:none;box-sizing:border-box}::slotted(ion-icon){line-height:1}.button-native::after{left:0;right:0;top:0;bottom:0;position:absolute;content:"";opacity:0}.button-inner{left:0;right:0;top:0;display:flex;position:absolute;flex-flow:row nowrap;flex-shrink:0;align-items:center;justify-content:center;height:100%;transition:all ease-in-out 300ms;transition-property:transform, opacity;z-index:1}:host(.fab-button-disabled){cursor:default;opacity:0.5;pointer-events:none}@media (any-hover: hover){:host(:hover) .button-native{color:var(--color-hover)}:host(:hover) .button-native::after{background:var(--background-hover);opacity:var(--background-hover-opacity)}}:host(.ion-focused) .button-native{color:var(--color-focused)}:host(.ion-focused) .button-native::after{background:var(--background-focused);opacity:var(--background-focused-opacity)}:host(.ion-activated) .button-native{color:var(--color-activated)}:host(.ion-activated) .button-native::after{background:var(--background-activated);opacity:var(--background-activated-opacity)}::slotted(ion-icon){line-height:1}:host(.fab-button-small){-webkit-margin-start:8px;margin-inline-start:8px;-webkit-margin-end:8px;margin-inline-end:8px;margin-top:8px;margin-bottom:8px;width:40px;height:40px}.close-icon{-webkit-margin-start:auto;margin-inline-start:auto;-webkit-margin-end:auto;margin-inline-end:auto;margin-top:0;margin-bottom:0;left:0;right:0;top:0;position:absolute;height:100%;transform:scale(0.4) rotateZ(-45deg);transition:all ease-in-out 300ms;transition-property:transform, opacity;font-size:var(--close-icon-font-size);opacity:0;z-index:1}:host(.fab-button-close-active) .close-icon{transform:scale(1) rotateZ(0deg);opacity:1}:host(.fab-button-close-active) .button-inner{transform:scale(0.4) rotateZ(45deg);opacity:0}ion-ripple-effect{color:var(--ripple-color)}@supports (backdrop-filter: blur(0)){:host(.fab-button-translucent) .button-native{backdrop-filter:var(--backdrop-filter)}}:host(.ion-color) .button-native{background:var(--ion-color-base);color:var(--ion-color-contrast)}:host{--background:var(--ion-color-primary, #3880ff);--background-activated:transparent;--background-focused:currentColor;--background-hover:currentColor;--background-activated-opacity:0;--background-focused-opacity:.24;--background-hover-opacity:.08;--color:var(--ion-color-primary-contrast, #fff);--box-shadow:0 3px 5px -1px rgba(0, 0, 0, 0.2), 0 6px 10px 0 rgba(0, 0, 0, 0.14), 0 1px 18px 0 rgba(0, 0, 0, 0.12);--transition:box-shadow 280ms cubic-bezier(0.4, 0, 0.2, 1), background-color 280ms cubic-bezier(0.4, 0, 0.2, 1), color 280ms cubic-bezier(0.4, 0, 0.2, 1), opacity 15ms linear 30ms, transform 270ms cubic-bezier(0, 0, 0.2, 1) 0ms;--close-icon-font-size:24px}:host(.ion-activated){--box-shadow:0 7px 8px -4px rgba(0, 0, 0, 0.2), 0 12px 17px 2px rgba(0, 0, 0, 0.14), 0 5px 22px 4px rgba(0, 0, 0, 0.12)}::slotted(ion-icon){font-size:24px}:host(.fab-button-in-list){--color:rgba(var(--ion-text-color-rgb, 0, 0, 0), 0.54);--color-activated:rgba(var(--ion-text-color-rgb, 0, 0, 0), 0.54);--color-focused:var(--color-activated);--background:var(--ion-color-light, #f4f5f8);--background-activated:transparent;--background-focused:var(--ion-color-light-shade, #d7d8da);--background-hover:var(--ion-color-light-tint, #f5f6f9)}:host(.fab-button-in-list) ::slotted(ion-icon){font-size:18px}:host(.ion-color.ion-focused) .button-native{color:var(--ion-color-contrast)}:host(.ion-color.ion-focused) .button-native::after{background:var(--ion-color-contrast)}:host(.ion-color.ion-activated) .button-native{color:var(--ion-color-contrast)}:host(.ion-color.ion-activated) .button-native::after{background:transparent}@media (any-hover: hover){:host(.ion-color:hover) .button-native{color:var(--ion-color-contrast)}:host(.ion-color:hover) .button-native::after{background:var(--ion-color-contrast)}}'}},601:function(o,t,n){n.d(t,{c:function(){return i},g:function(){return a},h:function(){return r},o:function(){return s}});const r=(o,t)=>null!==t.closest(o),i=(o,t)=>"string"==typeof o&&o.length>0?Object.assign({"ion-color":!0,[`ion-color-${o}`]:!0},t):t,a=o=>{const t={};return(o=>void 0!==o?(Array.isArray(o)?o:o.split(" ")).filter((o=>null!=o)).map((o=>o.trim())).filter((o=>""!==o)):[])(o).forEach((o=>t[o]=!0)),t},e=/^[a-z][a-z0-9+\-.]*:/,s=async(o,t,n,r)=>{if(null!=o&&"#"!==o[0]&&!e.test(o)){const i=document.querySelector("ion-router");if(i)return null!=t&&t.preventDefault(),i.push(o,n,r)}return!1}}}]);