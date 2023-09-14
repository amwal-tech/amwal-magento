/*! For license information please see 6166.bundle.js.LICENSE.txt */
"use strict";(self.webpackChunkamwal_magento_react=self.webpackChunkamwal_magento_react||[]).push([[6166],{4498:function(n,i,e){e.d(i,{E:function(){return d},s:function(){return t}});const t=n=>{try{if(n instanceof a)return n.value;if(!l()||"string"!=typeof n||""===n)return n;if(n.includes("onload="))return"";const i=document.createDocumentFragment(),e=document.createElement("div");i.appendChild(e),e.innerHTML=n,c.forEach((n=>{const e=i.querySelectorAll(n);for(let n=e.length-1;n>=0;n--){const t=e[n];t.parentNode?t.parentNode.removeChild(t):i.removeChild(t);const l=r(t);for(let n=0;n<l.length;n++)o(l[n])}}));const t=r(i);for(let n=0;n<t.length;n++)o(t[n]);const s=document.createElement("div");s.appendChild(i);const d=s.querySelector("div");return null!==d?d.innerHTML:s.innerHTML}catch(n){return console.error(n),""}},o=n=>{if(n.nodeType&&1!==n.nodeType)return;if("undefined"!=typeof NamedNodeMap&&!(n.attributes instanceof NamedNodeMap))return void n.remove();for(let i=n.attributes.length-1;i>=0;i--){const e=n.attributes.item(i),t=e.name;if(!s.includes(t.toLowerCase())){n.removeAttribute(t);continue}const o=e.value,r=n[t];(null!=o&&o.toLowerCase().includes("javascript:")||null!=r&&r.toLowerCase().includes("javascript:"))&&n.removeAttribute(t)}const i=r(n);for(let n=0;n<i.length;n++)o(i[n])},r=n=>null!=n.children?n.children:n.childNodes,l=()=>{var n;const i=window,e=null===(n=null==i?void 0:i.Ionic)||void 0===n?void 0:n.config;return!e||(e.get?e.get("sanitizerEnabled",!0):!0===e.sanitizerEnabled||void 0===e.sanitizerEnabled)},s=["class","id","href","src","name","slot"],c=["script","style","iframe","meta","link","object","embed"];class a{constructor(n){this.value=n}}const d=!1},6166:function(n,i,e){e.r(i),e.d(i,{ion_infinite_scroll_content:function(){return l}});var t=e(3542),o=e(4498),r=e(1209);const l=class{constructor(n){(0,t.r)(this,n),this.customHTMLEnabled=r.c.get("innerHTMLTemplatesEnabled",o.E),this.loadingSpinner=void 0,this.loadingText=void 0}componentDidLoad(){if(void 0===this.loadingSpinner){const n=(0,r.g)(this);this.loadingSpinner=r.c.get("infiniteLoadingSpinner",r.c.get("spinner","ios"===n?"lines":"crescent"))}}renderLoadingText(){const{customHTMLEnabled:n,loadingText:i}=this;return n?(0,t.h)("div",{class:"infinite-loading-text",innerHTML:(0,o.s)(i)}):(0,t.h)("div",{class:"infinite-loading-text"},this.loadingText)}render(){const n=(0,r.g)(this);return(0,t.h)(t.H,{class:{[n]:!0,[`infinite-scroll-content-${n}`]:!0}},(0,t.h)("div",{class:"infinite-loading"},this.loadingSpinner&&(0,t.h)("div",{class:"infinite-loading-spinner"},(0,t.h)("ion-spinner",{name:this.loadingSpinner})),void 0!==this.loadingText&&this.renderLoadingText()))}};l.style={ios:"ion-infinite-scroll-content{display:flex;flex-direction:column;justify-content:center;min-height:84px;text-align:center;user-select:none}.infinite-loading{margin-left:0;margin-right:0;margin-top:0;margin-bottom:32px;display:none;width:100%}.infinite-loading-text{-webkit-margin-start:32px;margin-inline-start:32px;-webkit-margin-end:32px;margin-inline-end:32px;margin-top:4px;margin-bottom:0}.infinite-scroll-loading ion-infinite-scroll-content>.infinite-loading{display:block}.infinite-scroll-content-ios .infinite-loading-text{color:var(--ion-color-step-600, #666666)}.infinite-scroll-content-ios .infinite-loading-spinner .spinner-lines-ios line,.infinite-scroll-content-ios .infinite-loading-spinner .spinner-lines-small-ios line,.infinite-scroll-content-ios .infinite-loading-spinner .spinner-crescent circle{stroke:var(--ion-color-step-600, #666666)}.infinite-scroll-content-ios .infinite-loading-spinner .spinner-bubbles circle,.infinite-scroll-content-ios .infinite-loading-spinner .spinner-circles circle,.infinite-scroll-content-ios .infinite-loading-spinner .spinner-dots circle{fill:var(--ion-color-step-600, #666666)}",md:"ion-infinite-scroll-content{display:flex;flex-direction:column;justify-content:center;min-height:84px;text-align:center;user-select:none}.infinite-loading{margin-left:0;margin-right:0;margin-top:0;margin-bottom:32px;display:none;width:100%}.infinite-loading-text{-webkit-margin-start:32px;margin-inline-start:32px;-webkit-margin-end:32px;margin-inline-end:32px;margin-top:4px;margin-bottom:0}.infinite-scroll-loading ion-infinite-scroll-content>.infinite-loading{display:block}.infinite-scroll-content-md .infinite-loading-text{color:var(--ion-color-step-600, #666666)}.infinite-scroll-content-md .infinite-loading-spinner .spinner-lines-md line,.infinite-scroll-content-md .infinite-loading-spinner .spinner-lines-small-md line,.infinite-scroll-content-md .infinite-loading-spinner .spinner-crescent circle{stroke:var(--ion-color-step-600, #666666)}.infinite-scroll-content-md .infinite-loading-spinner .spinner-bubbles circle,.infinite-scroll-content-md .infinite-loading-spinner .spinner-circles circle,.infinite-scroll-content-md .infinite-loading-spinner .spinner-dots circle{fill:var(--ion-color-step-600, #666666)}"}}}]);