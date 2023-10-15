/*! For license information please see 8592.bundle.js.LICENSE.txt */
"use strict";(self.webpackChunkamwal_magento_react=self.webpackChunkamwal_magento_react||[]).push([[8592],{8592:function(o,t,r){r.r(t),r.d(t,{ion_toolbar:function(){return e}});var n=r(3542),a=r(601),i=r(1209);const e=class{constructor(o){(0,n.r)(this,o),this.childrenStyles=new Map,this.color=void 0}componentWillLoad(){const o=Array.from(this.el.querySelectorAll("ion-buttons")),t=o.find((o=>"start"===o.slot));t&&t.classList.add("buttons-first-slot");const r=o.reverse(),n=r.find((o=>"end"===o.slot))||r.find((o=>"primary"===o.slot))||r.find((o=>"secondary"===o.slot));n&&n.classList.add("buttons-last-slot")}childrenStyle(o){o.stopPropagation();const t=o.target.tagName,r=o.detail,a={},i=this.childrenStyles.get(t)||{};let e=!1;Object.keys(r).forEach((o=>{const t=`toolbar-${o}`,n=r[o];n!==i[t]&&(e=!0),n&&(a[t]=!0)})),e&&(this.childrenStyles.set(t,a),(0,n.i)(this))}render(){const o=(0,i.g)(this),t={};return this.childrenStyles.forEach((o=>{Object.assign(t,o)})),(0,n.h)(n.H,{class:Object.assign(Object.assign({},t),(0,a.c)(this.color,{[o]:!0,"in-toolbar":(0,a.h)("ion-toolbar",this.el)}))},(0,n.h)("div",{class:"toolbar-background"}),(0,n.h)("div",{class:"toolbar-container"},(0,n.h)("slot",{name:"start"}),(0,n.h)("slot",{name:"secondary"}),(0,n.h)("div",{class:"toolbar-content"},(0,n.h)("slot",null)),(0,n.h)("slot",{name:"primary"}),(0,n.h)("slot",{name:"end"})))}get el(){return(0,n.e)(this)}};e.style={ios:":host{--border-width:0;--border-style:solid;--opacity:1;--opacity-scale:1;-moz-osx-font-smoothing:grayscale;-webkit-font-smoothing:antialiased;display:block;position:relative;width:100%;padding-right:var(--ion-safe-area-right);padding-left:var(--ion-safe-area-left);color:var(--color);font-family:var(--ion-font-family, inherit);contain:content;z-index:10;box-sizing:border-box}:host(.ion-color){color:var(--ion-color-contrast)}:host(.ion-color) .toolbar-background{background:var(--ion-color-base)}.toolbar-container{-webkit-padding-start:var(--padding-start);padding-inline-start:var(--padding-start);-webkit-padding-end:var(--padding-end);padding-inline-end:var(--padding-end);padding-top:var(--padding-top);padding-bottom:var(--padding-bottom);display:flex;position:relative;flex-direction:row;align-items:center;justify-content:space-between;width:100%;min-height:var(--min-height);contain:content;overflow:hidden;z-index:10;box-sizing:border-box}.toolbar-background{left:0;right:0;top:0;bottom:0;position:absolute;transform:translateZ(0);border-width:var(--border-width);border-style:var(--border-style);border-color:var(--border-color);background:var(--background);contain:strict;opacity:calc(var(--opacity) * var(--opacity-scale));z-index:-1;pointer-events:none}::slotted(ion-progress-bar){left:0;right:0;bottom:0;position:absolute}:host{--background:var(--ion-toolbar-background, var(--ion-color-step-50, #f7f7f7));--color:var(--ion-toolbar-color, var(--ion-text-color, #000));--border-color:var(--ion-toolbar-border-color, var(--ion-border-color, var(--ion-color-step-150, rgba(0, 0, 0, 0.2))));--padding-top:3px;--padding-bottom:3px;--padding-start:4px;--padding-end:4px;--min-height:44px}.toolbar-content{flex:1;order:4;min-width:0}:host(.toolbar-segment) .toolbar-content{display:inline-flex}:host(.toolbar-searchbar) .toolbar-container{padding-top:0;padding-bottom:0}:host(.toolbar-searchbar) ::slotted(*){align-self:start}:host(.toolbar-searchbar) ::slotted(ion-chip){margin-top:3px}:host(.toolbar-searchbar) ::slotted(ion-back-button){height:38px}::slotted(ion-buttons){min-height:38px}::slotted([slot=start]){order:2}::slotted([slot=secondary]){order:3}::slotted([slot=primary]){order:5;text-align:end}::slotted([slot=end]){order:6;text-align:end}:host(.toolbar-title-large){padding-bottom:7px}:host(.toolbar-title-large) .toolbar-container{flex-wrap:wrap;align-items:flex-start}:host(.toolbar-title-large) .toolbar-content ion-title{flex:1;order:8;min-width:100%}",md:":host{--border-width:0;--border-style:solid;--opacity:1;--opacity-scale:1;-moz-osx-font-smoothing:grayscale;-webkit-font-smoothing:antialiased;display:block;position:relative;width:100%;padding-right:var(--ion-safe-area-right);padding-left:var(--ion-safe-area-left);color:var(--color);font-family:var(--ion-font-family, inherit);contain:content;z-index:10;box-sizing:border-box}:host(.ion-color){color:var(--ion-color-contrast)}:host(.ion-color) .toolbar-background{background:var(--ion-color-base)}.toolbar-container{-webkit-padding-start:var(--padding-start);padding-inline-start:var(--padding-start);-webkit-padding-end:var(--padding-end);padding-inline-end:var(--padding-end);padding-top:var(--padding-top);padding-bottom:var(--padding-bottom);display:flex;position:relative;flex-direction:row;align-items:center;justify-content:space-between;width:100%;min-height:var(--min-height);contain:content;overflow:hidden;z-index:10;box-sizing:border-box}.toolbar-background{left:0;right:0;top:0;bottom:0;position:absolute;transform:translateZ(0);border-width:var(--border-width);border-style:var(--border-style);border-color:var(--border-color);background:var(--background);contain:strict;opacity:calc(var(--opacity) * var(--opacity-scale));z-index:-1;pointer-events:none}::slotted(ion-progress-bar){left:0;right:0;bottom:0;position:absolute}:host{--background:var(--ion-toolbar-background, var(--ion-background-color, #fff));--color:var(--ion-toolbar-color, var(--ion-text-color, #424242));--border-color:var(--ion-toolbar-border-color, var(--ion-border-color, var(--ion-color-step-150, #c1c4cd)));--padding-top:0;--padding-bottom:0;--padding-start:0;--padding-end:0;--min-height:56px}.toolbar-content{flex:1;order:3;min-width:0;max-width:100%}::slotted(.buttons-first-slot){-webkit-margin-start:4px;margin-inline-start:4px}::slotted(.buttons-last-slot){-webkit-margin-end:4px;margin-inline-end:4px}::slotted([slot=start]){order:2}::slotted([slot=secondary]){order:4}::slotted([slot=primary]){order:5;text-align:end}::slotted([slot=end]){order:6;text-align:end}"}},601:function(o,t,r){r.d(t,{c:function(){return a},g:function(){return i},h:function(){return n},o:function(){return l}});const n=(o,t)=>null!==t.closest(o),a=(o,t)=>"string"==typeof o&&o.length>0?Object.assign({"ion-color":!0,[`ion-color-${o}`]:!0},t):t,i=o=>{const t={};return(o=>void 0!==o?(Array.isArray(o)?o:o.split(" ")).filter((o=>null!=o)).map((o=>o.trim())).filter((o=>""!==o)):[])(o).forEach((o=>t[o]=!0)),t},e=/^[a-z][a-z0-9+\-.]*:/,l=async(o,t,r,n)=>{if(null!=o&&"#"!==o[0]&&!e.test(o)){const a=document.querySelector("ion-router");if(a)return null!=t&&t.preventDefault(),a.push(o,r,n)}return!1}}}]);