/*! For license information please see 6708.bundle.js.LICENSE.txt */
"use strict";(self.webpackChunkamwal_magento_react=self.webpackChunkamwal_magento_react||[]).push([[6708],{1983:function(o,n,i){i.d(n,{a:function(){return e},b:function(){return r},p:function(){return t}});const t=(o,...n)=>console.warn(`[Ionic Warning]: ${o}`,...n),e=(o,...n)=>console.error(`[Ionic Error]: ${o}`,...n),r=(o,...n)=>console.error(`<${o.tagName.toLowerCase()}> must be used inside ${n.join(" or ")}.`)},6708:function(o,n,i){i.r(n),i.d(n,{ion_accordion_group:function(){return a}});var t=i(2170),e=i(399),r=i(1983);const a=class{constructor(o){(0,t.r)(this,o),this.ionChange=(0,t.d)(this,"ionChange",7),this.ionValueChange=(0,t.d)(this,"ionValueChange",7),this.animated=!0,this.multiple=void 0,this.value=void 0,this.disabled=!1,this.readonly=!1,this.expand="compact"}valueChanged(){const{value:o,multiple:n}=this;!n&&Array.isArray(o)&&(0,r.p)(`ion-accordion-group was passed an array of values, but multiple="false". This is incorrect usage and may result in unexpected behaviors. To dismiss this warning, pass a string to the "value" property when multiple="false".\n\n  Value Passed: [${o.map((o=>`'${o}'`)).join(", ")}]\n`,this.el),this.ionValueChange.emit({value:this.value})}async disabledChanged(){const{disabled:o}=this,n=await this.getAccordions();for(const i of n)i.disabled=o}async readonlyChanged(){const{readonly:o}=this,n=await this.getAccordions();for(const i of n)i.readonly=o}async onKeydown(o){const n=document.activeElement;if(!n)return;if(!n.closest('ion-accordion [slot="header"]'))return;const i="ION-ACCORDION"===n.tagName?n:n.closest("ion-accordion");if(!i)return;if(i.closest("ion-accordion-group")!==this.el)return;const t=await this.getAccordions(),e=t.findIndex((o=>o===i));if(-1===e)return;let r;"ArrowDown"===o.key?r=this.findNextAccordion(t,e):"ArrowUp"===o.key?r=this.findPreviousAccordion(t,e):"Home"===o.key?r=t[0]:"End"===o.key&&(r=t[t.length-1]),void 0!==r&&r!==n&&r.focus()}async componentDidLoad(){this.disabled&&this.disabledChanged(),this.readonly&&this.readonlyChanged()}setValue(o){const n=this.value=o;this.ionChange.emit({value:n})}async requestAccordionToggle(o,n){const{multiple:i,value:t,readonly:e,disabled:r}=this;if(!e&&!r)if(n)if(i){const n=null!=t?t:[],i=Array.isArray(n)?n:[n];void 0===i.find((n=>n===o))&&void 0!==o&&this.setValue([...i,o])}else this.setValue(o);else if(i){const n=null!=t?t:[],i=Array.isArray(n)?n:[n];this.setValue(i.filter((n=>n!==o)))}else this.setValue(void 0)}findNextAccordion(o,n){const i=o[n+1];return void 0===i?o[0]:i}findPreviousAccordion(o,n){const i=o[n-1];return void 0===i?o[o.length-1]:i}async getAccordions(){return Array.from(this.el.querySelectorAll(":scope > ion-accordion"))}render(){const{disabled:o,readonly:n,expand:i}=this,r=(0,e.g)(this);return(0,t.h)(t.H,{class:{[r]:!0,"accordion-group-disabled":o,"accordion-group-readonly":n,[`accordion-group-expand-${i}`]:!0},role:"presentation"},(0,t.h)("slot",null))}get el(){return(0,t.e)(this)}static get watchers(){return{value:["valueChanged"],disabled:["disabledChanged"],readonly:["readonlyChanged"]}}};a.style={ios:":host{display:block}:host(.accordion-group-expand-inset){-webkit-margin-start:16px;margin-inline-start:16px;-webkit-margin-end:16px;margin-inline-end:16px;margin-top:16px;margin-bottom:16px}:host(.accordion-group-expand-inset) ::slotted(ion-accordion.accordion-expanding),:host(.accordion-group-expand-inset) ::slotted(ion-accordion.accordion-expanded){border-bottom:none}",md:":host{display:block}:host(.accordion-group-expand-inset){-webkit-margin-start:16px;margin-inline-start:16px;-webkit-margin-end:16px;margin-inline-end:16px;margin-top:16px;margin-bottom:16px}:host(.accordion-group-expand-inset) ::slotted(ion-accordion){box-shadow:0px 3px 1px -2px rgba(0, 0, 0, 0.2), 0px 2px 2px 0px rgba(0, 0, 0, 0.14), 0px 1px 5px 0px rgba(0, 0, 0, 0.12)}:host(.accordion-group-expand-inset) ::slotted(ion-accordion.accordion-expanding),:host(.accordion-group-expand-inset) ::slotted(ion-accordion.accordion-expanded){margin-left:0;margin-right:0;margin-top:16px;margin-bottom:16px;border-radius:6px}:host(.accordion-group-expand-inset) ::slotted(ion-accordion.accordion-previous){border-bottom-right-radius:6px;border-bottom-left-radius:6px}:host-context([dir=rtl]):host(.accordion-group-expand-inset) ::slotted(ion-accordion.accordion-previous),:host-context([dir=rtl]).accordion-group-expand-inset ::slotted(ion-accordion.accordion-previous){border-bottom-right-radius:6px;border-bottom-left-radius:6px}:host(.accordion-group-expand-inset) ::slotted(ion-accordion.accordion-next){border-top-left-radius:6px;border-top-right-radius:6px}:host-context([dir=rtl]):host(.accordion-group-expand-inset) ::slotted(ion-accordion.accordion-next),:host-context([dir=rtl]).accordion-group-expand-inset ::slotted(ion-accordion.accordion-next){border-top-left-radius:6px;border-top-right-radius:6px}:host(.accordion-group-expand-inset) ::slotted(ion-accordion):first-of-type{margin-left:0;margin-right:0;margin-top:0;margin-bottom:0}"}}}]);