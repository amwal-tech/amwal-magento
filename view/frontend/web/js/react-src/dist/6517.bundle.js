/*! For license information please see 6517.bundle.js.LICENSE.txt */
"use strict";(self.webpackChunkamwal_magento_react=self.webpackChunkamwal_magento_react||[]).push([[6517],{8248:function(o,t,i){i.d(t,{a:function(){return d},b:function(){return p},c:function(){return e},d:function(){return s},e:function(){return f},f:function(){return v},g:function(){return h},h:function(){return g},i:function(){return c},j:function(){return w},k:function(){return B},l:function(){return b},m:function(){return M},n:function(){return x},o:function(){return a},p:function(){return m},q:function(){return z},r:function(){return u},s:function(){return _},t:function(){return r},u:function(){return y},v:function(){return k}});const r=(o,t=0)=>new Promise((i=>{n(o,t,i)})),n=(o,t=0,i)=>{let r,n;const e={passive:!0},a=()=>{r&&r()},s=t=>{void 0!==t&&o!==t.target||(a(),i(t))};return o&&(o.addEventListener("webkitTransitionEnd",s,e),o.addEventListener("transitionend",s,e),n=setTimeout(s,t+500),r=()=>{n&&(clearTimeout(n),n=void 0),o.removeEventListener("webkitTransitionEnd",s,e),o.removeEventListener("transitionend",s,e)}),a},e=(o,t)=>{o.componentOnReady?o.componentOnReady().then((o=>t(o))):p((()=>t(o)))},a=o=>void 0!==o.componentOnReady,s=(o,t=[])=>{const i={};return t.forEach((t=>{o.hasAttribute(t)&&(null!==o.getAttribute(t)&&(i[t]=o.getAttribute(t)),o.removeAttribute(t))})),i},l=["role","aria-activedescendant","aria-atomic","aria-autocomplete","aria-braillelabel","aria-brailleroledescription","aria-busy","aria-checked","aria-colcount","aria-colindex","aria-colindextext","aria-colspan","aria-controls","aria-current","aria-describedby","aria-description","aria-details","aria-disabled","aria-errormessage","aria-expanded","aria-flowto","aria-haspopup","aria-hidden","aria-invalid","aria-keyshortcuts","aria-label","aria-labelledby","aria-level","aria-live","aria-multiline","aria-multiselectable","aria-orientation","aria-owns","aria-placeholder","aria-posinset","aria-pressed","aria-readonly","aria-relevant","aria-required","aria-roledescription","aria-rowcount","aria-rowindex","aria-rowindextext","aria-rowspan","aria-selected","aria-setsize","aria-sort","aria-valuemax","aria-valuemin","aria-valuenow","aria-valuetext"],c=(o,t)=>{let i=l;return t&&t.length>0&&(i=i.filter((o=>!t.includes(o)))),s(o,i)},d=(o,t,i,r)=>{var n;if("undefined"!=typeof window){const e=window,a=null===(n=null==e?void 0:e.Ionic)||void 0===n?void 0:n.config;if(a){const n=a.get("_ael");if(n)return n(o,t,i,r);if(a._ael)return a._ael(o,t,i,r)}}return o.addEventListener(t,i,r)},u=(o,t,i,r)=>{var n;if("undefined"!=typeof window){const e=window,a=null===(n=null==e?void 0:e.Ionic)||void 0===n?void 0:n.config;if(a){const n=a.get("_rel");if(n)return n(o,t,i,r);if(a._rel)return a._rel(o,t,i,r)}}return o.removeEventListener(t,i,r)},h=(o,t=o)=>o.shadowRoot||t,p=o=>"function"==typeof __zone_symbol__requestAnimationFrame?__zone_symbol__requestAnimationFrame(o):"function"==typeof requestAnimationFrame?requestAnimationFrame(o):setTimeout(o),g=o=>!!o.shadowRoot&&!!o.attachShadow,b=o=>{const t=o.closest("ion-item");return t?t.querySelector("ion-label"):null},v=o=>{if(o.focus(),o.classList.contains("ion-focusable")){const t=o.closest("ion-app");t&&t.setFocus([o])}},m=(o,t)=>{let i;const r=o.getAttribute("aria-labelledby"),n=o.id;let e=null!==r&&""!==r.trim()?r:t+"-lbl",a=null!==r&&""!==r.trim()?document.getElementById(r):b(o);return a?(null===r&&(a.id=e),i=a.textContent,a.setAttribute("aria-hidden","true")):""!==n.trim()&&(a=document.querySelector(`label[for="${n}"]`),a&&(""!==a.id?e=a.id:a.id=e=`${n}-lbl`,i=a.textContent)),{label:a,labelId:e,labelText:i}},f=(o,t,i,r,n)=>{if(o||g(t)){let o=t.querySelector("input.aux-input");o||(o=t.ownerDocument.createElement("input"),o.type="hidden",o.classList.add("aux-input"),t.appendChild(o)),o.disabled=n,o.name=i,o.value=r||""}},w=(o,t,i)=>Math.max(o,Math.min(t,i)),x=(o,t)=>{if(!o){const o="ASSERT: "+t;throw console.error(o),new Error(o)}},k=o=>o.timeStamp||Date.now(),y=o=>{if(o){const t=o.changedTouches;if(t&&t.length>0){const o=t[0];return{x:o.clientX,y:o.clientY}}if(void 0!==o.pageX)return{x:o.pageX,y:o.pageY}}return{x:0,y:0}},M=o=>{const t="rtl"===document.dir;switch(o){case"start":return t;case"end":return!t;default:throw new Error(`"${o}" is not a valid value for [side]. Use "start" or "end" instead.`)}},B=(o,t)=>{const i=o._original||o;return{_original:o,emit:z(i.emit.bind(i),t)}},z=(o,t=0)=>{let i;return(...r)=>{clearTimeout(i),i=setTimeout(o,t,...r)}},_=(o,t)=>{if(null!=o||(o={}),null!=t||(t={}),o===t)return!0;const i=Object.keys(o);if(i.length!==Object.keys(t).length)return!1;for(const r of i){if(!(r in t))return!1;if(o[r]!==t[r])return!1}return!0}},2839:function(o,t,i){i.d(t,{a:function(){return c},b:function(){return r},c:function(){return d},d:function(){return p},e:function(){return f},f:function(){return h},g:function(){return s},h:function(){return a},i:function(){return g},j:function(){return b},k:function(){return v},l:function(){return x},m:function(){return w},n:function(){return e},o:function(){return n},p:function(){return M},q:function(){return z},r:function(){return y},s:function(){return B},t:function(){return u},u:function(){return l},v:function(){return k},w:function(){return m}});const r="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path stroke-linecap='square' stroke-miterlimit='10' stroke-width='48' d='M244 400L100 256l144-144M120 256h292' class='ionicon-fill-none'/></svg>",n="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='48' d='M112 268l144 144 144-144M256 392V100' class='ionicon-fill-none'/></svg>",e="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path d='M368 64L144 256l224 192V64z'/></svg>",a="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path d='M64 144l192 224 192-224H64z'/></svg>",s="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path d='M448 368L256 144 64 368h384z'/></svg>",l="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path stroke-linecap='round' stroke-linejoin='round' d='M416 128L192 384l-96-96' class='ionicon-fill-none ionicon-stroke-width'/></svg>",c="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='48' d='M328 112L184 256l144 144' class='ionicon-fill-none'/></svg>",d="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='48' d='M112 184l144 144 144-144' class='ionicon-fill-none'/></svg>",u="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path d='M136 208l120-104 120 104M136 304l120 104 120-104' stroke-width='48' stroke-linecap='round' stroke-linejoin='round' class='ionicon-fill-none'/></svg>",h="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='48' d='M184 112l144 144-144 144' class='ionicon-fill-none'/></svg>",p="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='48' d='M184 112l144 144-144 144' class='ionicon-fill-none'/></svg>",g="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path d='M289.94 256l95-95A24 24 0 00351 127l-95 95-95-95a24 24 0 00-34 34l95 95-95 95a24 24 0 1034 34l95-95 95 95a24 24 0 0034-34z'/></svg>",b="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path d='M256 48C141.31 48 48 141.31 48 256s93.31 208 208 208 208-93.31 208-208S370.69 48 256 48zm75.31 260.69a16 16 0 11-22.62 22.62L256 278.63l-52.69 52.68a16 16 0 01-22.62-22.62L233.37 256l-52.68-52.69a16 16 0 0122.62-22.62L256 233.37l52.69-52.68a16 16 0 0122.62 22.62L278.63 256z'/></svg>",v="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path d='M400 145.49L366.51 112 256 222.51 145.49 112 112 145.49 222.51 256 112 366.51 145.49 400 256 289.49 366.51 400 400 366.51 289.49 256 400 145.49z'/></svg>",m="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><circle cx='256' cy='256' r='192' stroke-linecap='round' stroke-linejoin='round' class='ionicon-fill-none ionicon-stroke-width'/></svg>",f="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><circle cx='256' cy='256' r='48'/><circle cx='416' cy='256' r='48'/><circle cx='96' cy='256' r='48'/></svg>",w="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path stroke-linecap='round' stroke-miterlimit='10' d='M80 160h352M80 256h352M80 352h352' class='ionicon-fill-none ionicon-stroke-width'/></svg>",x="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path d='M64 384h384v-42.67H64zm0-106.67h384v-42.66H64zM64 128v42.67h384V128z'/></svg>",k="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path stroke-linecap='round' stroke-linejoin='round' d='M400 256H112' class='ionicon-fill-none ionicon-stroke-width'/></svg>",y="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path stroke-linecap='round' stroke-linejoin='round' d='M96 256h320M96 176h320M96 336h320' class='ionicon-fill-none ionicon-stroke-width'/></svg>",M="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path stroke-linecap='square' stroke-linejoin='round' stroke-width='44' d='M118 304h276M118 208h276' class='ionicon-fill-none'/></svg>",B="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path d='M221.09 64a157.09 157.09 0 10157.09 157.09A157.1 157.1 0 00221.09 64z' stroke-miterlimit='10' class='ionicon-fill-none ionicon-stroke-width'/><path stroke-linecap='round' stroke-miterlimit='10' d='M338.29 338.29L448 448' class='ionicon-fill-none ionicon-stroke-width'/></svg>",z="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><path d='M464 428L339.92 303.9a160.48 160.48 0 0030.72-94.58C370.64 120.37 298.27 48 209.32 48S48 120.37 48 209.32s72.37 161.32 161.32 161.32a160.48 160.48 0 0094.58-30.72L428 464zM209.32 319.69a110.38 110.38 0 11110.37-110.37 110.5 110.5 0 01-110.37 110.37z'/></svg>"},6517:function(o,t,i){i.r(t),i.d(t,{ion_breadcrumb:function(){return l}});var r=i(2170),n=i(2839),e=i(399),a=i(8248),s=i(601);const l=class{constructor(o){(0,r.r)(this,o),this.ionFocus=(0,r.d)(this,"ionFocus",7),this.ionBlur=(0,r.d)(this,"ionBlur",7),this.collapsedClick=(0,r.d)(this,"collapsedClick",7),this.inheritedAttributes={},this.onFocus=()=>{this.ionFocus.emit()},this.onBlur=()=>{this.ionBlur.emit()},this.collapsedIndicatorClick=()=>{this.collapsedClick.emit({ionShadowTarget:this.collapsedRef})},this.collapsed=!1,this.last=void 0,this.showCollapsedIndicator=void 0,this.color=void 0,this.active=!1,this.disabled=!1,this.download=void 0,this.href=void 0,this.rel=void 0,this.separator=void 0,this.target=void 0,this.routerDirection="forward",this.routerAnimation=void 0}componentWillLoad(){this.inheritedAttributes=(0,a.i)(this.el)}isClickable(){return void 0!==this.href}render(){const{color:o,active:t,collapsed:i,disabled:a,download:l,el:c,inheritedAttributes:d,last:u,routerAnimation:h,routerDirection:p,separator:g,showCollapsedIndicator:b,target:v}=this,m=this.isClickable(),f=void 0===this.href?"span":"a",w=a?void 0:this.href,x=(0,e.g)(this),k="span"===f?{}:{download:l,href:w,target:v},y=!u&&(i?!(!b||u):g);return(0,r.h)(r.H,{onClick:o=>(0,s.o)(w,o,p,h),"aria-disabled":a?"true":null,class:(0,s.c)(o,{[x]:!0,"breadcrumb-active":t,"breadcrumb-collapsed":i,"breadcrumb-disabled":a,"in-breadcrumbs-color":(0,s.h)("ion-breadcrumbs[color]",c),"in-toolbar":(0,s.h)("ion-toolbar",this.el),"in-toolbar-color":(0,s.h)("ion-toolbar[color]",this.el),"ion-activatable":m,"ion-focusable":m})},(0,r.h)(f,Object.assign({},k,{class:"breadcrumb-native",part:"native",disabled:a,onFocus:this.onFocus,onBlur:this.onBlur},d),(0,r.h)("slot",{name:"start"}),(0,r.h)("slot",null),(0,r.h)("slot",{name:"end"})),b&&(0,r.h)("button",{part:"collapsed-indicator","aria-label":"Show more breadcrumbs",onClick:()=>this.collapsedIndicatorClick(),ref:o=>this.collapsedRef=o,class:{"breadcrumbs-collapsed-indicator":!0}},(0,r.h)("ion-icon",{"aria-hidden":"true",icon:n.e,lazy:!1})),y&&(0,r.h)("span",{class:"breadcrumb-separator",part:"separator","aria-hidden":"true"},(0,r.h)("slot",{name:"separator"},"ios"===x?(0,r.h)("ion-icon",{icon:n.d,lazy:!1,"flip-rtl":!0}):(0,r.h)("span",null,"/"))))}get el(){return(0,r.e)(this)}};l.style={ios:":host{display:flex;flex:0 0 auto;align-items:center;color:var(--color);font-size:16px;font-weight:400;line-height:1.5}.breadcrumb-native{font-family:inherit;font-size:inherit;font-style:inherit;font-weight:inherit;letter-spacing:inherit;text-decoration:inherit;text-indent:inherit;text-overflow:inherit;text-transform:inherit;text-align:inherit;white-space:inherit;color:inherit;padding-left:0;padding-right:0;padding-top:0;padding-bottom:0;margin-left:0;margin-right:0;margin-top:0;margin-bottom:0;display:flex;align-items:center;width:100%;outline:none;background:inherit}:host(.breadcrumb-disabled){cursor:default;opacity:0.5;pointer-events:none}:host(.breadcrumb-active){color:var(--color-active)}:host(.ion-focused){color:var(--color-focused)}:host(.ion-focused) .breadcrumb-native{background:var(--background-focused)}@media (any-hover: hover){:host(.ion-activatable:hover){color:var(--color-hover)}:host(.ion-activatable.in-breadcrumbs-color:hover),:host(.ion-activatable.ion-color:hover){color:var(--ion-color-shade)}}.breadcrumb-separator{display:inline-flex}:host(.breadcrumb-collapsed) .breadcrumb-native{display:none}:host(.in-breadcrumbs-color),:host(.in-breadcrumbs-color.breadcrumb-active){color:var(--ion-color-base)}:host(.in-breadcrumbs-color) .breadcrumb-separator{color:var(--ion-color-base)}:host(.ion-color){color:var(--ion-color-base)}:host(.in-toolbar-color),:host(.in-toolbar-color) .breadcrumb-separator{color:rgba(var(--ion-color-contrast-rgb), 0.8)}:host(.in-toolbar-color.breadcrumb-active){color:var(--ion-color-contrast)}.breadcrumbs-collapsed-indicator{padding-left:0;padding-right:0;padding-top:0;padding-bottom:0;-webkit-margin-start:14px;margin-inline-start:14px;-webkit-margin-end:14px;margin-inline-end:14px;margin-top:0;margin-bottom:0;display:flex;flex:1 1 100%;align-items:center;justify-content:center;width:32px;height:18px;border:0;outline:none;cursor:pointer;appearance:none}.breadcrumbs-collapsed-indicator ion-icon{margin-top:1px;font-size:22px}:host{--color:var(--ion-color-step-850, #2d4665);--color-active:var(--ion-text-color, #03060b);--color-hover:var(--ion-text-color, #03060b);--color-focused:var(--color-active);--background-focused:var(--ion-color-step-50, rgba(233, 237, 243, 0.7))}:host(.breadcrumb-active){font-weight:600}.breadcrumb-native{border-radius:4px;-webkit-padding-start:12px;padding-inline-start:12px;-webkit-padding-end:12px;padding-inline-end:12px;padding-top:5px;padding-bottom:5px;border:1px solid transparent}:host(.ion-focused) .breadcrumb-native{border-radius:8px}:host(.in-breadcrumbs-color.ion-focused) .breadcrumb-native,:host(.ion-color.ion-focused) .breadcrumb-native{background:rgba(var(--ion-color-base-rgb), 0.1);color:var(--ion-color-base)}:host(.ion-focused) ::slotted(ion-icon),:host(.in-breadcrumbs-color.ion-focused) ::slotted(ion-icon),:host(.ion-color.ion-focused) ::slotted(ion-icon){color:var(--ion-color-step-750, #445b78)}.breadcrumb-separator{color:var(--ion-color-step-550, #73849a)}::slotted(ion-icon){color:var(--ion-color-step-400, #92a0b3);font-size:18px}::slotted(ion-icon[slot=start]){-webkit-margin-end:8px;margin-inline-end:8px}::slotted(ion-icon[slot=end]){-webkit-margin-start:8px;margin-inline-start:8px}:host(.breadcrumb-active) ::slotted(ion-icon){color:var(--ion-color-step-850, #242d39)}.breadcrumbs-collapsed-indicator{border-radius:4px;background:var(--ion-color-step-100, #e9edf3);color:var(--ion-color-step-550, #73849a)}.breadcrumbs-collapsed-indicator:hover{opacity:0.45}.breadcrumbs-collapsed-indicator:focus{background:var(--ion-color-step-150, #d9e0ea)}",md:":host{display:flex;flex:0 0 auto;align-items:center;color:var(--color);font-size:16px;font-weight:400;line-height:1.5}.breadcrumb-native{font-family:inherit;font-size:inherit;font-style:inherit;font-weight:inherit;letter-spacing:inherit;text-decoration:inherit;text-indent:inherit;text-overflow:inherit;text-transform:inherit;text-align:inherit;white-space:inherit;color:inherit;padding-left:0;padding-right:0;padding-top:0;padding-bottom:0;margin-left:0;margin-right:0;margin-top:0;margin-bottom:0;display:flex;align-items:center;width:100%;outline:none;background:inherit}:host(.breadcrumb-disabled){cursor:default;opacity:0.5;pointer-events:none}:host(.breadcrumb-active){color:var(--color-active)}:host(.ion-focused){color:var(--color-focused)}:host(.ion-focused) .breadcrumb-native{background:var(--background-focused)}@media (any-hover: hover){:host(.ion-activatable:hover){color:var(--color-hover)}:host(.ion-activatable.in-breadcrumbs-color:hover),:host(.ion-activatable.ion-color:hover){color:var(--ion-color-shade)}}.breadcrumb-separator{display:inline-flex}:host(.breadcrumb-collapsed) .breadcrumb-native{display:none}:host(.in-breadcrumbs-color),:host(.in-breadcrumbs-color.breadcrumb-active){color:var(--ion-color-base)}:host(.in-breadcrumbs-color) .breadcrumb-separator{color:var(--ion-color-base)}:host(.ion-color){color:var(--ion-color-base)}:host(.in-toolbar-color),:host(.in-toolbar-color) .breadcrumb-separator{color:rgba(var(--ion-color-contrast-rgb), 0.8)}:host(.in-toolbar-color.breadcrumb-active){color:var(--ion-color-contrast)}.breadcrumbs-collapsed-indicator{padding-left:0;padding-right:0;padding-top:0;padding-bottom:0;-webkit-margin-start:14px;margin-inline-start:14px;-webkit-margin-end:14px;margin-inline-end:14px;margin-top:0;margin-bottom:0;display:flex;flex:1 1 100%;align-items:center;justify-content:center;width:32px;height:18px;border:0;outline:none;cursor:pointer;appearance:none}.breadcrumbs-collapsed-indicator ion-icon{margin-top:1px;font-size:22px}:host{--color:var(--ion-color-step-600, #677483);--color-active:var(--ion-text-color, #03060b);--color-hover:var(--ion-text-color, #03060b);--color-focused:var(--ion-color-step-800, #35404e);--background-focused:$breadcrumb-md-background-focused}:host(.breadcrumb-active){font-weight:500}.breadcrumb-native{-webkit-padding-start:12px;padding-inline-start:12px;-webkit-padding-end:12px;padding-inline-end:12px;padding-top:6px;padding-bottom:6px}.breadcrumb-separator{-webkit-margin-start:10px;margin-inline-start:10px;-webkit-margin-end:10px;margin-inline-end:10px;margin-top:-1px}:host(.ion-focused) .breadcrumb-native{border-radius:4px;box-shadow:0px 1px 2px rgba(0, 0, 0, 0.2), 0px 2px 8px rgba(0, 0, 0, 0.12)}.breadcrumb-separator{color:var(--ion-color-step-550, #73849a)}::slotted(ion-icon){color:var(--ion-color-step-550, #7d8894);font-size:18px}::slotted(ion-icon[slot=start]){-webkit-margin-end:8px;margin-inline-end:8px}::slotted(ion-icon[slot=end]){-webkit-margin-start:8px;margin-inline-start:8px}:host(.breadcrumb-active) ::slotted(ion-icon){color:var(--ion-color-step-850, #222d3a)}.breadcrumbs-collapsed-indicator{border-radius:2px;background:var(--ion-color-step-100, #eef1f3);color:var(--ion-color-step-550, #73849a)}.breadcrumbs-collapsed-indicator:hover{opacity:0.7}.breadcrumbs-collapsed-indicator:focus{background:var(--ion-color-step-150, #dfe5e8)}"}},601:function(o,t,i){i.d(t,{c:function(){return n},g:function(){return e},h:function(){return r},o:function(){return s}});const r=(o,t)=>null!==t.closest(o),n=(o,t)=>"string"==typeof o&&o.length>0?Object.assign({"ion-color":!0,[`ion-color-${o}`]:!0},t):t,e=o=>{const t={};return(o=>void 0!==o?(Array.isArray(o)?o:o.split(" ")).filter((o=>null!=o)).map((o=>o.trim())).filter((o=>""!==o)):[])(o).forEach((o=>t[o]=!0)),t},a=/^[a-z][a-z0-9+\-.]*:/,s=async(o,t,i,r)=>{if(null!=o&&"#"!==o[0]&&!a.test(o)){const n=document.querySelector("ion-router");if(n)return null!=t&&t.preventDefault(),n.push(o,i,r)}return!1}}}]);