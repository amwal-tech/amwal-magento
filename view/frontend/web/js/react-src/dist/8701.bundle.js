/*! For license information please see 8701.bundle.js.LICENSE.txt */
"use strict";(self.webpackChunkamwal_magento_react=self.webpackChunkamwal_magento_react||[]).push([[8701],{8248:function(t,o,n){n.d(o,{a:function(){return d},b:function(){return g},c:function(){return r},d:function(){return c},e:function(){return f},f:function(){return v},g:function(){return b},h:function(){return h},i:function(){return l},j:function(){return k},k:function(){return E},l:function(){return m},m:function(){return _},n:function(){return x},o:function(){return a},p:function(){return p},q:function(){return z},r:function(){return u},s:function(){return S},t:function(){return e},u:function(){return w},v:function(){return y}});const e=(t,o=0)=>new Promise((n=>{i(t,o,n)})),i=(t,o=0,n)=>{let e,i;const r={passive:!0},a=()=>{e&&e()},c=o=>{void 0!==o&&t!==o.target||(a(),n(o))};return t&&(t.addEventListener("webkitTransitionEnd",c,r),t.addEventListener("transitionend",c,r),i=setTimeout(c,o+500),e=()=>{i&&(clearTimeout(i),i=void 0),t.removeEventListener("webkitTransitionEnd",c,r),t.removeEventListener("transitionend",c,r)}),a},r=(t,o)=>{t.componentOnReady?t.componentOnReady().then((t=>o(t))):g((()=>o(t)))},a=t=>void 0!==t.componentOnReady,c=(t,o=[])=>{const n={};return o.forEach((o=>{t.hasAttribute(o)&&(null!==t.getAttribute(o)&&(n[o]=t.getAttribute(o)),t.removeAttribute(o))})),n},s=["role","aria-activedescendant","aria-atomic","aria-autocomplete","aria-braillelabel","aria-brailleroledescription","aria-busy","aria-checked","aria-colcount","aria-colindex","aria-colindextext","aria-colspan","aria-controls","aria-current","aria-describedby","aria-description","aria-details","aria-disabled","aria-errormessage","aria-expanded","aria-flowto","aria-haspopup","aria-hidden","aria-invalid","aria-keyshortcuts","aria-label","aria-labelledby","aria-level","aria-live","aria-multiline","aria-multiselectable","aria-orientation","aria-owns","aria-placeholder","aria-posinset","aria-pressed","aria-readonly","aria-relevant","aria-required","aria-roledescription","aria-rowcount","aria-rowindex","aria-rowindextext","aria-rowspan","aria-selected","aria-setsize","aria-sort","aria-valuemax","aria-valuemin","aria-valuenow","aria-valuetext"],l=(t,o)=>{let n=s;return o&&o.length>0&&(n=n.filter((t=>!o.includes(t)))),c(t,n)},d=(t,o,n,e)=>{var i;if("undefined"!=typeof window){const r=window,a=null===(i=null==r?void 0:r.Ionic)||void 0===i?void 0:i.config;if(a){const i=a.get("_ael");if(i)return i(t,o,n,e);if(a._ael)return a._ael(t,o,n,e)}}return t.addEventListener(o,n,e)},u=(t,o,n,e)=>{var i;if("undefined"!=typeof window){const r=window,a=null===(i=null==r?void 0:r.Ionic)||void 0===i?void 0:i.config;if(a){const i=a.get("_rel");if(i)return i(t,o,n,e);if(a._rel)return a._rel(t,o,n,e)}}return t.removeEventListener(o,n,e)},b=(t,o=t)=>t.shadowRoot||o,g=t=>"function"==typeof __zone_symbol__requestAnimationFrame?__zone_symbol__requestAnimationFrame(t):"function"==typeof requestAnimationFrame?requestAnimationFrame(t):setTimeout(t),h=t=>!!t.shadowRoot&&!!t.attachShadow,m=t=>{const o=t.closest("ion-item");return o?o.querySelector("ion-label"):null},v=t=>{if(t.focus(),t.classList.contains("ion-focusable")){const o=t.closest("ion-app");o&&o.setFocus([t])}},p=(t,o)=>{let n;const e=t.getAttribute("aria-labelledby"),i=t.id;let r=null!==e&&""!==e.trim()?e:o+"-lbl",a=null!==e&&""!==e.trim()?document.getElementById(e):m(t);return a?(null===e&&(a.id=r),n=a.textContent,a.setAttribute("aria-hidden","true")):""!==i.trim()&&(a=document.querySelector(`label[for="${i}"]`),a&&(""!==a.id?r=a.id:a.id=r=`${i}-lbl`,n=a.textContent)),{label:a,labelId:r,labelText:n}},f=(t,o,n,e,i)=>{if(t||h(o)){let t=o.querySelector("input.aux-input");t||(t=o.ownerDocument.createElement("input"),t.type="hidden",t.classList.add("aux-input"),o.appendChild(t)),t.disabled=i,t.name=n,t.value=e||""}},k=(t,o,n)=>Math.max(t,Math.min(o,n)),x=(t,o)=>{if(!t){const t="ASSERT: "+o;throw console.error(t),new Error(t)}},y=t=>t.timeStamp||Date.now(),w=t=>{if(t){const o=t.changedTouches;if(o&&o.length>0){const t=o[0];return{x:t.clientX,y:t.clientY}}if(void 0!==t.pageX)return{x:t.pageX,y:t.pageY}}return{x:0,y:0}},_=t=>{const o="rtl"===document.dir;switch(t){case"start":return o;case"end":return!o;default:throw new Error(`"${t}" is not a valid value for [side]. Use "start" or "end" instead.`)}},E=(t,o)=>{const n=t._original||t;return{_original:t,emit:z(n.emit.bind(n),o)}},z=(t,o=0)=>{let n;return(...e)=>{clearTimeout(n),n=setTimeout(t,o,...e)}},S=(t,o)=>{if(null!=t||(t={}),null!=o||(o={}),t===o)return!0;const n=Object.keys(t);if(n.length!==Object.keys(o).length)return!1;for(const e of n){if(!(e in o))return!1;if(t[e]!==o[e])return!1}return!0}},8701:function(t,o,n){n.r(o),n.d(o,{ion_segment_button:function(){return s}});var e=n(2170),i=n(399),r=n(8248),a=n(601);let c=0;const s=class{constructor(t){(0,e.r)(this,t),this.segmentEl=null,this.inheritedAttributes={},this.updateStyle=()=>{(0,e.i)(this)},this.updateState=()=>{const{segmentEl:t}=this;t&&(this.checked=t.value===this.value,t.disabled&&(this.disabled=!0))},this.checked=!1,this.disabled=!1,this.layout="icon-top",this.type="button",this.value="ion-sb-"+c++}valueChanged(){this.updateState()}connectedCallback(){const t=this.segmentEl=this.el.closest("ion-segment");t&&(this.updateState(),(0,r.a)(t,"ionSelect",this.updateState),(0,r.a)(t,"ionStyle",this.updateStyle))}disconnectedCallback(){const t=this.segmentEl;t&&((0,r.r)(t,"ionSelect",this.updateState),(0,r.r)(t,"ionStyle",this.updateStyle),this.segmentEl=null)}componentWillLoad(){this.inheritedAttributes=Object.assign({},(0,r.d)(this.el,["aria-label"]))}get hasLabel(){return!!this.el.querySelector("ion-label")}get hasIcon(){return!!this.el.querySelector("ion-icon")}async setFocus(){const{nativeEl:t}=this;void 0!==t&&t.focus()}render(){const{checked:t,type:o,disabled:n,hasIcon:r,hasLabel:c,layout:s,segmentEl:l}=this,d=(0,i.g)(this);return(0,e.h)(e.H,{class:{[d]:!0,"in-toolbar":(0,a.h)("ion-toolbar",this.el),"in-toolbar-color":(0,a.h)("ion-toolbar[color]",this.el),"in-segment":(0,a.h)("ion-segment",this.el),"in-segment-color":void 0!==(null==l?void 0:l.color),"segment-button-has-label":c,"segment-button-has-icon":r,"segment-button-has-label-only":c&&!r,"segment-button-has-icon-only":r&&!c,"segment-button-disabled":n,"segment-button-checked":t,[`segment-button-layout-${s}`]:!0,"ion-activatable":!0,"ion-activatable-instant":!0,"ion-focusable":!0}},(0,e.h)("button",Object.assign({"aria-selected":t?"true":"false",role:"tab",ref:t=>this.nativeEl=t,type:o,class:"button-native",part:"native",disabled:n},this.inheritedAttributes),(0,e.h)("span",{class:"button-inner"},(0,e.h)("slot",null)),"md"===d&&(0,e.h)("ion-ripple-effect",null)),(0,e.h)("div",{part:"indicator",class:{"segment-button-indicator":!0,"segment-button-indicator-animated":!0}},(0,e.h)("div",{part:"indicator-background",class:"segment-button-indicator-background"})))}get el(){return(0,e.e)(this)}static get watchers(){return{value:["valueChanged"]}}};s.style={ios:':host{--color:initial;--color-hover:var(--color);--color-checked:var(--color);--color-disabled:var(--color);--padding-start:0;--padding-end:0;--padding-top:0;--padding-bottom:0;border-radius:var(--border-radius);display:flex;position:relative;flex:1 1 auto;flex-direction:column;height:auto;background:var(--background);color:var(--color);text-decoration:none;text-overflow:ellipsis;white-space:nowrap;font-kerning:none;cursor:pointer}.button-native{border-radius:0;font-family:inherit;font-size:inherit;font-style:inherit;font-weight:inherit;letter-spacing:inherit;text-decoration:inherit;text-indent:inherit;text-overflow:inherit;text-transform:inherit;text-align:inherit;white-space:inherit;color:inherit;-webkit-margin-start:var(--margin-start);margin-inline-start:var(--margin-start);-webkit-margin-end:var(--margin-end);margin-inline-end:var(--margin-end);margin-top:var(--margin-top);margin-bottom:var(--margin-bottom);-webkit-padding-start:var(--padding-start);padding-inline-start:var(--padding-start);-webkit-padding-end:var(--padding-end);padding-inline-end:var(--padding-end);padding-top:var(--padding-top);padding-bottom:var(--padding-bottom);transform:translate3d(0,  0,  0);display:flex;position:relative;flex-direction:inherit;flex-grow:1;align-items:center;justify-content:center;width:100%;min-width:inherit;max-width:inherit;height:auto;min-height:inherit;max-height:inherit;transition:var(--transition);border:none;outline:none;background:transparent;contain:content;pointer-events:none;overflow:hidden;z-index:2}.button-native::after{left:0;right:0;top:0;bottom:0;position:absolute;content:"";opacity:0}.button-inner{display:flex;position:relative;flex-flow:inherit;align-items:center;justify-content:center;width:100%;height:100%;z-index:1}:host(.segment-button-checked){background:var(--background-checked);color:var(--color-checked)}:host(.segment-button-disabled){cursor:default;pointer-events:none}:host(.ion-focused) .button-native{color:var(--color-focused)}:host(.ion-focused) .button-native::after{background:var(--background-focused);opacity:var(--background-focused-opacity)}:host(:focus){outline:none}@media (any-hover: hover){:host(:hover) .button-native{color:var(--color-hover)}:host(:hover) .button-native::after{background:var(--background-hover);opacity:var(--background-hover-opacity)}:host(.segment-button-checked:hover) .button-native{color:var(--color-checked)}}::slotted(ion-icon){flex-shrink:0;order:-1;pointer-events:none}::slotted(ion-label){display:block;align-self:center;line-height:22px;text-overflow:ellipsis;white-space:nowrap;box-sizing:border-box;pointer-events:none}:host(.segment-button-layout-icon-top) .button-native{flex-direction:column}:host(.segment-button-layout-icon-start) .button-native{flex-direction:row}:host(.segment-button-layout-icon-end) .button-native{flex-direction:row-reverse}:host(.segment-button-layout-icon-bottom) .button-native{flex-direction:column-reverse}:host(.segment-button-layout-icon-hide) ::slotted(ion-icon){display:none}:host(.segment-button-layout-label-hide) ::slotted(ion-label){display:none}ion-ripple-effect{color:var(--ripple-color, var(--color-checked))}.segment-button-indicator{transform-origin:left;position:absolute;opacity:0;box-sizing:border-box;will-change:transform, opacity;pointer-events:none}.segment-button-indicator-background{width:100%;height:var(--indicator-height);transform:var(--indicator-transform);box-shadow:var(--indicator-box-shadow);pointer-events:none}.segment-button-indicator-animated{transition:var(--indicator-transition)}:host(.segment-button-checked) .segment-button-indicator{opacity:1}@media (prefers-reduced-motion: reduce){.segment-button-indicator-background{transform:none}.segment-button-indicator-animated{transition:none}}:host{--background:none;--background-checked:none;--background-hover:none;--background-hover-opacity:0;--background-focused:none;--background-focused-opacity:0;--border-radius:7px;--border-width:1px;--border-color:rgba(var(--ion-text-color-rgb, 0, 0, 0), 0.12);--border-style:solid;--indicator-box-shadow:0 0 5px rgba(0, 0, 0, 0.16);--indicator-color:var(--ion-color-step-350, var(--ion-background-color, #fff));--indicator-height:100%;--indicator-transition:transform 260ms cubic-bezier(0.4, 0, 0.2, 1);--indicator-transform:none;--transition:100ms all linear;--padding-top:0;--padding-end:13px;--padding-bottom:0;--padding-start:13px;margin-top:2px;margin-bottom:2px;position:relative;flex-basis:0;flex-direction:row;min-width:70px;min-height:28px;transform:translate3d(0, 0, 0);font-size:13px;font-weight:450;line-height:37px}:host::before{margin-left:0;margin-right:0;margin-top:5px;margin-bottom:5px;transition:160ms opacity ease-in-out;transition-delay:100ms;border-left:var(--border-width) var(--border-style) var(--border-color);content:"";opacity:1;will-change:opacity}:host(:first-of-type)::before{border-left-color:transparent}:host(.segment-button-disabled){opacity:0.3}::slotted(ion-icon){font-size:24px}:host(.segment-button-layout-icon-start) ::slotted(ion-label){-webkit-margin-start:2px;margin-inline-start:2px;-webkit-margin-end:0;margin-inline-end:0}:host(.segment-button-layout-icon-end) ::slotted(ion-label){-webkit-margin-start:0;margin-inline-start:0;-webkit-margin-end:2px;margin-inline-end:2px}.segment-button-indicator{-webkit-padding-start:2px;padding-inline-start:2px;-webkit-padding-end:2px;padding-inline-end:2px;left:0;right:0;top:0;bottom:0}.segment-button-indicator-background{border-radius:var(--border-radius);background:var(--indicator-color)}.segment-button-indicator-background{transition:var(--indicator-transition)}:host(.segment-button-checked)::before,:host(.segment-button-after-checked)::before{opacity:0}:host(.segment-button-checked){z-index:-1}:host(.segment-button-activated){--indicator-transform:scale(0.95)}:host(.ion-focused) .button-native{opacity:0.7}@media (any-hover: hover){:host(:hover) .button-native{opacity:0.5}:host(.segment-button-checked:hover) .button-native{opacity:1}}:host(.in-segment-color){background:none;color:var(--ion-text-color, #000)}:host(.in-segment-color) .segment-button-indicator-background{background:var(--ion-color-step-350, var(--ion-background-color, #fff))}@media (any-hover: hover){:host(.in-segment-color:hover) .button-native,:host(.in-segment-color.segment-button-checked:hover) .button-native{color:var(--ion-text-color, #000)}}:host(.in-toolbar:not(.in-segment-color)){--background-checked:var(--ion-toolbar-segment-background-checked, none);--color:var(--ion-toolbar-segment-color, var(--ion-toolbar-color), initial);--color-checked:var(--ion-toolbar-segment-color-checked, var(--ion-toolbar-color), initial);--indicator-color:var(--ion-toolbar-segment-indicator-color, var(--ion-color-step-350, var(--ion-background-color, #fff)))}:host(.in-toolbar-color) .segment-button-indicator-background{background:var(--ion-color-contrast)}:host(.in-toolbar-color:not(.in-segment-color)) .button-native{color:var(--ion-color-contrast)}:host(.in-toolbar-color.segment-button-checked:not(.in-segment-color)) .button-native{color:var(--ion-color-base)}@media (any-hover: hover){:host(.in-toolbar-color:not(.in-segment-color):hover) .button-native{color:var(--ion-color-contrast)}:host(.in-toolbar-color.segment-button-checked:not(.in-segment-color):hover) .button-native{color:var(--ion-color-base)}}',md:':host{--color:initial;--color-hover:var(--color);--color-checked:var(--color);--color-disabled:var(--color);--padding-start:0;--padding-end:0;--padding-top:0;--padding-bottom:0;border-radius:var(--border-radius);display:flex;position:relative;flex:1 1 auto;flex-direction:column;height:auto;background:var(--background);color:var(--color);text-decoration:none;text-overflow:ellipsis;white-space:nowrap;font-kerning:none;cursor:pointer}.button-native{border-radius:0;font-family:inherit;font-size:inherit;font-style:inherit;font-weight:inherit;letter-spacing:inherit;text-decoration:inherit;text-indent:inherit;text-overflow:inherit;text-transform:inherit;text-align:inherit;white-space:inherit;color:inherit;-webkit-margin-start:var(--margin-start);margin-inline-start:var(--margin-start);-webkit-margin-end:var(--margin-end);margin-inline-end:var(--margin-end);margin-top:var(--margin-top);margin-bottom:var(--margin-bottom);-webkit-padding-start:var(--padding-start);padding-inline-start:var(--padding-start);-webkit-padding-end:var(--padding-end);padding-inline-end:var(--padding-end);padding-top:var(--padding-top);padding-bottom:var(--padding-bottom);transform:translate3d(0,  0,  0);display:flex;position:relative;flex-direction:inherit;flex-grow:1;align-items:center;justify-content:center;width:100%;min-width:inherit;max-width:inherit;height:auto;min-height:inherit;max-height:inherit;transition:var(--transition);border:none;outline:none;background:transparent;contain:content;pointer-events:none;overflow:hidden;z-index:2}.button-native::after{left:0;right:0;top:0;bottom:0;position:absolute;content:"";opacity:0}.button-inner{display:flex;position:relative;flex-flow:inherit;align-items:center;justify-content:center;width:100%;height:100%;z-index:1}:host(.segment-button-checked){background:var(--background-checked);color:var(--color-checked)}:host(.segment-button-disabled){cursor:default;pointer-events:none}:host(.ion-focused) .button-native{color:var(--color-focused)}:host(.ion-focused) .button-native::after{background:var(--background-focused);opacity:var(--background-focused-opacity)}:host(:focus){outline:none}@media (any-hover: hover){:host(:hover) .button-native{color:var(--color-hover)}:host(:hover) .button-native::after{background:var(--background-hover);opacity:var(--background-hover-opacity)}:host(.segment-button-checked:hover) .button-native{color:var(--color-checked)}}::slotted(ion-icon){flex-shrink:0;order:-1;pointer-events:none}::slotted(ion-label){display:block;align-self:center;line-height:22px;text-overflow:ellipsis;white-space:nowrap;box-sizing:border-box;pointer-events:none}:host(.segment-button-layout-icon-top) .button-native{flex-direction:column}:host(.segment-button-layout-icon-start) .button-native{flex-direction:row}:host(.segment-button-layout-icon-end) .button-native{flex-direction:row-reverse}:host(.segment-button-layout-icon-bottom) .button-native{flex-direction:column-reverse}:host(.segment-button-layout-icon-hide) ::slotted(ion-icon){display:none}:host(.segment-button-layout-label-hide) ::slotted(ion-label){display:none}ion-ripple-effect{color:var(--ripple-color, var(--color-checked))}.segment-button-indicator{transform-origin:left;position:absolute;opacity:0;box-sizing:border-box;will-change:transform, opacity;pointer-events:none}.segment-button-indicator-background{width:100%;height:var(--indicator-height);transform:var(--indicator-transform);box-shadow:var(--indicator-box-shadow);pointer-events:none}.segment-button-indicator-animated{transition:var(--indicator-transition)}:host(.segment-button-checked) .segment-button-indicator{opacity:1}@media (prefers-reduced-motion: reduce){.segment-button-indicator-background{transform:none}.segment-button-indicator-animated{transition:none}}:host{--background:none;--background-checked:none;--background-hover:var(--color-checked);--background-focused:var(--color-checked);--background-activated-opacity:0;--background-focused-opacity:.12;--background-hover-opacity:.04;--color:rgba(var(--ion-text-color-rgb, 0, 0, 0), 0.6);--color-checked:var(--ion-color-primary, #3880ff);--indicator-box-shadow:none;--indicator-color:var(--color-checked);--indicator-height:2px;--indicator-transition:transform 250ms cubic-bezier(0.4, 0, 0.2, 1);--indicator-transform:none;--padding-top:0;--padding-end:16px;--padding-bottom:0;--padding-start:16px;--transition:color 0.15s linear 0s, opacity 0.15s linear 0s;min-width:90px;max-width:360px;min-height:48px;border-width:var(--border-width);border-style:var(--border-style);border-color:var(--border-color);font-size:14px;font-weight:500;letter-spacing:0.06em;line-height:40px;text-transform:uppercase}:host(.segment-button-disabled){opacity:0.3}:host(.in-segment-color){background:none;color:rgba(var(--ion-text-color-rgb, 0, 0, 0), 0.6)}:host(.in-segment-color) ion-ripple-effect{color:var(--ion-color-base)}:host(.in-segment-color) .segment-button-indicator-background{background:var(--ion-color-base)}:host(.in-segment-color.segment-button-checked) .button-native{color:var(--ion-color-base)}:host(.in-segment-color.ion-focused) .button-native::after{background:var(--ion-color-base)}@media (any-hover: hover){:host(.in-segment-color:hover) .button-native{color:rgba(var(--ion-text-color-rgb, 0, 0, 0), 0.6)}:host(.in-segment-color:hover) .button-native::after{background:var(--ion-color-base)}:host(.in-segment-color.segment-button-checked:hover) .button-native{color:var(--ion-color-base)}}:host(.in-toolbar:not(.in-segment-color)){--background:var(--ion-toolbar-segment-background, none);--background-checked:var(--ion-toolbar-segment-background-checked, none);--color:var(--ion-toolbar-segment-color, rgba(var(--ion-text-color-rgb, 0, 0, 0), 0.6));--color-checked:var(--ion-toolbar-segment-color-checked, var(--ion-color-primary, #3880ff));--indicator-color:var(--ion-toolbar-segment-color-checked, var(--color-checked))}:host(.in-toolbar-color:not(.in-segment-color)) .button-native{color:rgba(var(--ion-color-contrast-rgb), 0.6)}:host(.in-toolbar-color.segment-button-checked:not(.in-segment-color)) .button-native{color:var(--ion-color-contrast)}@media (any-hover: hover){:host(.in-toolbar-color:not(.in-segment-color)) .button-native::after{background:var(--ion-color-contrast)}}::slotted(ion-icon){margin-top:12px;margin-bottom:12px;font-size:24px}::slotted(ion-label){margin-top:12px;margin-bottom:12px}:host(.segment-button-layout-icon-top) ::slotted(ion-label),:host(.segment-button-layout-icon-bottom) ::slotted(ion-icon){margin-top:0}:host(.segment-button-layout-icon-top) ::slotted(ion-icon),:host(.segment-button-layout-icon-bottom) ::slotted(ion-label){margin-bottom:0}:host(.segment-button-layout-icon-start) ::slotted(ion-label){-webkit-margin-start:8px;margin-inline-start:8px;-webkit-margin-end:0;margin-inline-end:0}:host(.segment-button-layout-icon-end) ::slotted(ion-label){-webkit-margin-start:0;margin-inline-start:0;-webkit-margin-end:8px;margin-inline-end:8px}:host(.segment-button-has-icon-only) ::slotted(ion-icon){margin-top:12px;margin-bottom:12px}:host(.segment-button-has-label-only) ::slotted(ion-label){margin-top:12px;margin-bottom:12px}.segment-button-indicator{left:0;right:0;bottom:0}.segment-button-indicator-background{background:var(--indicator-color)}:host(.in-toolbar:not(.in-segment-color)) .segment-button-indicator-background{background:var(--ion-toolbar-segment-indicator-color, var(--indicator-color))}:host(.in-toolbar-color:not(.in-segment-color)) .segment-button-indicator-background{background:var(--ion-color-contrast)}'}},601:function(t,o,n){n.d(o,{c:function(){return i},g:function(){return r},h:function(){return e},o:function(){return c}});const e=(t,o)=>null!==o.closest(t),i=(t,o)=>"string"==typeof t&&t.length>0?Object.assign({"ion-color":!0,[`ion-color-${t}`]:!0},o):o,r=t=>{const o={};return(t=>void 0!==t?(Array.isArray(t)?t:t.split(" ")).filter((t=>null!=t)).map((t=>t.trim())).filter((t=>""!==t)):[])(t).forEach((t=>o[t]=!0)),o},a=/^[a-z][a-z0-9+\-.]*:/,c=async(t,o,n,e)=>{if(null!=t&&"#"!==t[0]&&!a.test(t)){const i=document.querySelector("ion-router");if(i)return null!=o&&o.preventDefault(),i.push(t,n,e)}return!1}}}]);