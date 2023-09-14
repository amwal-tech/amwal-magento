/*! For license information please see 8900.bundle.js.LICENSE.txt */
"use strict";(self.webpackChunkamwal_magento_react=self.webpackChunkamwal_magento_react||[]).push([[8900],{1170:function(o,t,r){r.d(t,{g:function(){return e}});var n=r(261);const e=()=>{if(void 0!==n.w)return n.w.Capacitor}},261:function(o,t,r){r.d(t,{d:function(){return e},w:function(){return n}});const n="undefined"!=typeof window?window:void 0,e="undefined"!=typeof document?document:void 0},8900:function(o,t,r){r.r(t),r.d(t,{ion_tab_bar:function(){return c}});var n=r(3542),e=r(7509),a=r(601),i=r(1209);r(261),r(4918),r(1170);const c=class{constructor(o){(0,n.r)(this,o),this.ionTabBarChanged=(0,n.d)(this,"ionTabBarChanged",7),this.keyboardCtrl=null,this.keyboardVisible=!1,this.color=void 0,this.selectedTab=void 0,this.translucent=!1}selectedTabChanged(){void 0!==this.selectedTab&&this.ionTabBarChanged.emit({tab:this.selectedTab})}componentWillLoad(){this.selectedTabChanged()}async connectedCallback(){this.keyboardCtrl=await(0,e.c)((async(o,t)=>{!1===o&&void 0!==t&&await t,this.keyboardVisible=o}))}disconnectedCallback(){this.keyboardCtrl&&this.keyboardCtrl.destroy()}render(){const{color:o,translucent:t,keyboardVisible:r}=this,e=(0,i.g)(this),c=r&&"top"!==this.el.getAttribute("slot");return(0,n.h)(n.H,{role:"tablist","aria-hidden":c?"true":null,class:(0,a.c)(o,{[e]:!0,"tab-bar-translucent":t,"tab-bar-hidden":c})},(0,n.h)("slot",null))}get el(){return(0,n.e)(this)}static get watchers(){return{selectedTab:["selectedTabChanged"]}}};c.style={ios:":host{-webkit-padding-start:var(--ion-safe-area-left);padding-inline-start:var(--ion-safe-area-left);-webkit-padding-end:var(--ion-safe-area-right);padding-inline-end:var(--ion-safe-area-right);display:flex;align-items:center;justify-content:center;width:auto;padding-bottom:var(--ion-safe-area-bottom, 0);border-top:var(--border);background:var(--background);color:var(--color);text-align:center;contain:strict;user-select:none;z-index:10;box-sizing:content-box !important}:host(.ion-color) ::slotted(ion-tab-button){--background-focused:var(--ion-color-shade);--color-selected:var(--ion-color-contrast)}:host(.ion-color) ::slotted(.tab-selected){color:var(--ion-color-contrast)}:host(.ion-color),:host(.ion-color) ::slotted(ion-tab-button){color:rgba(var(--ion-color-contrast-rgb), 0.7)}:host(.ion-color),:host(.ion-color) ::slotted(ion-tab-button){background:var(--ion-color-base)}:host(.ion-color) ::slotted(ion-tab-button.ion-focused),:host(.tab-bar-translucent) ::slotted(ion-tab-button.ion-focused){background:var(--background-focused)}:host(.tab-bar-translucent) ::slotted(ion-tab-button){background:transparent}:host([slot=top]){padding-top:var(--ion-safe-area-top, 0);padding-bottom:0;border-top:0;border-bottom:var(--border)}:host(.tab-bar-hidden){display:none !important}:host{--background:var(--ion-tab-bar-background, var(--ion-color-step-50, #f7f7f7));--background-focused:var(--ion-tab-bar-background-focused, #e0e0e0);--border:0.55px solid var(--ion-tab-bar-border-color, var(--ion-border-color, var(--ion-color-step-150, rgba(0, 0, 0, 0.2))));--color:var(--ion-tab-bar-color, var(--ion-color-step-600, #666666));--color-selected:var(--ion-tab-bar-color-selected, var(--ion-color-primary, #3880ff));height:50px}@supports (backdrop-filter: blur(0)){:host(.tab-bar-translucent){--background:rgba(var(--ion-background-color-rgb, 255, 255, 255), 0.8);backdrop-filter:saturate(210%) blur(20px)}:host(.ion-color.tab-bar-translucent){background:rgba(var(--ion-color-base-rgb), 0.8)}:host(.tab-bar-translucent) ::slotted(ion-tab-button.ion-focused){background:rgba(var(--ion-background-color-rgb, 255, 255, 255), 0.6)}}",md:":host{-webkit-padding-start:var(--ion-safe-area-left);padding-inline-start:var(--ion-safe-area-left);-webkit-padding-end:var(--ion-safe-area-right);padding-inline-end:var(--ion-safe-area-right);display:flex;align-items:center;justify-content:center;width:auto;padding-bottom:var(--ion-safe-area-bottom, 0);border-top:var(--border);background:var(--background);color:var(--color);text-align:center;contain:strict;user-select:none;z-index:10;box-sizing:content-box !important}:host(.ion-color) ::slotted(ion-tab-button){--background-focused:var(--ion-color-shade);--color-selected:var(--ion-color-contrast)}:host(.ion-color) ::slotted(.tab-selected){color:var(--ion-color-contrast)}:host(.ion-color),:host(.ion-color) ::slotted(ion-tab-button){color:rgba(var(--ion-color-contrast-rgb), 0.7)}:host(.ion-color),:host(.ion-color) ::slotted(ion-tab-button){background:var(--ion-color-base)}:host(.ion-color) ::slotted(ion-tab-button.ion-focused),:host(.tab-bar-translucent) ::slotted(ion-tab-button.ion-focused){background:var(--background-focused)}:host(.tab-bar-translucent) ::slotted(ion-tab-button){background:transparent}:host([slot=top]){padding-top:var(--ion-safe-area-top, 0);padding-bottom:0;border-top:0;border-bottom:var(--border)}:host(.tab-bar-hidden){display:none !important}:host{--background:var(--ion-tab-bar-background, var(--ion-background-color, #fff));--background-focused:var(--ion-tab-bar-background-focused, #e0e0e0);--border:1px solid var(--ion-tab-bar-border-color, var(--ion-border-color, var(--ion-color-step-150, rgba(0, 0, 0, 0.07))));--color:var(--ion-tab-bar-color, var(--ion-color-step-650, #595959));--color-selected:var(--ion-tab-bar-color-selected, var(--ion-color-primary, #3880ff));height:56px}"}},7509:function(o,t,r){r.d(t,{c:function(){return c}});var n=r(261),e=r(4918);const a=o=>{if(void 0===n.d||o===e.a.None||void 0===o)return null;const t=n.d.querySelector("ion-app");return null!=t?t:n.d.body},i=o=>{const t=a(o);return null===t?0:t.clientHeight},c=async o=>{let t,r,c,s;const d=async()=>{const o=await e.K.getResizeMode(),a=void 0===o?void 0:o.mode;t=()=>{void 0===s&&(s=i(a)),c=!0,l(c,a)},r=()=>{c=!1,l(c,a)},null===n.w||void 0===n.w||n.w.addEventListener("keyboardWillShow",t),null===n.w||void 0===n.w||n.w.addEventListener("keyboardWillHide",r)},l=(t,r)=>{o&&o(t,b(r))},b=o=>{if(0===s||s===i(o))return;const t=a(o);return null!==t?new Promise((o=>{const r=new ResizeObserver((()=>{t.clientHeight===s&&(r.disconnect(),o())}));r.observe(t)})):void 0};return await d(),{init:d,destroy:()=>{null===n.w||void 0===n.w||n.w.removeEventListener("keyboardWillShow",t),null===n.w||void 0===n.w||n.w.removeEventListener("keyboardWillHide",r),t=r=void 0},isKeyboardVisible:()=>c}}},4918:function(o,t,r){r.d(t,{K:function(){return i},a:function(){return e}});var n,e,a=r(1170);!function(o){o.Unimplemented="UNIMPLEMENTED",o.Unavailable="UNAVAILABLE"}(n||(n={})),function(o){o.Body="body",o.Ionic="ionic",o.Native="native",o.None="none"}(e||(e={}));const i={getEngine(){const o=(0,a.g)();if(null==o?void 0:o.isPluginAvailable("Keyboard"))return o.Plugins.Keyboard},getResizeMode(){const o=this.getEngine();return(null==o?void 0:o.getResizeMode)?o.getResizeMode().catch((o=>{if(o.code!==n.Unimplemented)throw o})):Promise.resolve(void 0)}}},601:function(o,t,r){r.d(t,{c:function(){return e},g:function(){return a},h:function(){return n},o:function(){return c}});const n=(o,t)=>null!==t.closest(o),e=(o,t)=>"string"==typeof o&&o.length>0?Object.assign({"ion-color":!0,[`ion-color-${o}`]:!0},t):t,a=o=>{const t={};return(o=>void 0!==o?(Array.isArray(o)?o:o.split(" ")).filter((o=>null!=o)).map((o=>o.trim())).filter((o=>""!==o)):[])(o).forEach((o=>t[o]=!0)),t},i=/^[a-z][a-z0-9+\-.]*:/,c=async(o,t,r,n)=>{if(null!=o&&"#"!==o[0]&&!i.test(o)){const e=document.querySelector("ion-router");if(e)return null!=t&&t.preventDefault(),e.push(o,r,n)}return!1}}}]);