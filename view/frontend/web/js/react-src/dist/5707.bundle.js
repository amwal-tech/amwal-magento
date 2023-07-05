"use strict";(self.webpackChunkamwal_magento_react=self.webpackChunkamwal_magento_react||[]).push([[5707],{5707:function(o,t,e){e.r(t),e.d(t,{ion_icon:function(){return f}});var i=e(2170);let n;const r=(o,t,e,i,n)=>(e="ios"===(e&&a(e))?"ios":"md",i&&"ios"===e?o=a(i):n&&"md"===e?o=a(n):(o||!t||c(t)||(o=t),l(o)&&(o=a(o))),l(o)&&""!==o.trim()?""!==o.replace(/[a-z]|-|\d/gi,"")?null:o:null),s=o=>l(o)&&(o=o.trim(),c(o))?o:null,c=o=>o.length>0&&/(\/|\.)/.test(o),l=o=>"string"==typeof o,a=o=>o.toLowerCase(),d=o=>{if(1===o.nodeType){if("script"===o.nodeName.toLowerCase())return!1;for(let t=0;t<o.attributes.length;t++){const e=o.attributes[t].name;if(l(e)&&0===e.toLowerCase().indexOf("on"))return!1}for(let t=0;t<o.childNodes.length;t++)if(!d(o.childNodes[t]))return!1}return!0},h=new Map,u=new Map;let m;const f=class{constructor(o){(0,i.r)(this,o),this.iconName=null,this.inheritedAttributes={},this.isVisible=!1,this.mode=g(),this.lazy=!1,this.sanitize=!0}componentWillLoad(){this.inheritedAttributes=((o,t=[])=>{const e={};return t.forEach((t=>{o.hasAttribute(t)&&(null!==o.getAttribute(t)&&(e[t]=o.getAttribute(t)),o.removeAttribute(t))})),e})(this.el,["aria-label"])}connectedCallback(){this.waitUntilVisible(this.el,"50px",(()=>{this.isVisible=!0,this.loadIcon()}))}disconnectedCallback(){this.io&&(this.io.disconnect(),this.io=void 0)}waitUntilVisible(o,t,e){if(this.lazy&&"undefined"!=typeof window&&window.IntersectionObserver){const i=this.io=new window.IntersectionObserver((o=>{o[0].isIntersecting&&(i.disconnect(),this.io=void 0,e())}),{rootMargin:t});i.observe(o)}else e()}loadIcon(){if(this.isVisible){const o=(o=>{let t=s(o.src);if(t)return t;if(t=r(o.name,o.icon,o.mode,o.ios,o.md),t)return e=t,(()=>{if("undefined"==typeof window)return new Map;if(!n){const o=window;o.Ionicons=o.Ionicons||{},n=o.Ionicons.map=o.Ionicons.map||new Map}return n})().get(e)||(0,i.j)(`svg/${e}.svg`);var e;if(o.icon){if(t=s(o.icon),t)return t;if(t=s(o.icon[o.mode]),t)return t}return null})(this);o&&(h.has(o)?this.svgContent=h.get(o):((o,t)=>{let e=u.get(o);if(!e){if("undefined"==typeof fetch||"undefined"==typeof document)return h.set(o,""),Promise.resolve();if((o=>o.startsWith("data:image/svg+xml"))(o)&&(o=>-1!==o.indexOf(";utf8,"))(o)){m||(m=new DOMParser);const t=m.parseFromString(o,"text/html").querySelector("svg");return t&&h.set(o,t.outerHTML),Promise.resolve()}e=fetch(o).then((e=>{if(e.ok)return e.text().then((e=>{e&&!1!==t&&(e=(o=>{const t=document.createElement("div");t.innerHTML=o;for(let o=t.childNodes.length-1;o>=0;o--)"svg"!==t.childNodes[o].nodeName.toLowerCase()&&t.removeChild(t.childNodes[o]);const e=t.firstElementChild;if(e&&"svg"===e.nodeName.toLowerCase()){const o=e.getAttribute("class")||"";if(e.setAttribute("class",(o+" s-ion-icon").trim()),d(e))return t.innerHTML}return""})(e)),h.set(o,e||"")}));h.set(o,"")})),u.set(o,e)}return e})(o,this.sanitize).then((()=>this.svgContent=h.get(o))))}this.iconName=r(this.name,this.icon,this.mode,this.ios,this.md)}render(){const{iconName:o,el:t,inheritedAttributes:e}=this,n=this.mode||"md",r=this.flipRtl||o&&(o.indexOf("arrow")>-1||o.indexOf("chevron")>-1)&&!1!==this.flipRtl;return(0,i.h)(i.H,Object.assign({role:"img",class:Object.assign(Object.assign({[n]:!0},b(this.color)),{[`icon-${this.size}`]:!!this.size,"flip-rtl":!!r&&(s=t,s&&""!==s.dir?"rtl"===s.dir.toLowerCase():"rtl"===(null===document||void 0===document?void 0:document.dir.toLowerCase()))})},e),this.svgContent?(0,i.h)("div",{class:"icon-inner",innerHTML:this.svgContent}):(0,i.h)("div",{class:"icon-inner"}));var s}static get assetsDirs(){return["svg"]}get el(){return(0,i.e)(this)}static get watchers(){return{name:["loadIcon"],src:["loadIcon"],icon:["loadIcon"],ios:["loadIcon"],md:["loadIcon"]}}},g=()=>"undefined"!=typeof document&&document.documentElement.getAttribute("mode")||"md",b=o=>o?{"ion-color":!0,[`ion-color-${o}`]:!0}:null;f.style=":host{display:inline-block;width:1em;height:1em;contain:strict;fill:currentColor;box-sizing:content-box !important}:host .ionicon{stroke:currentColor}.ionicon-fill-none{fill:none}.ionicon-stroke-width{stroke-width:32px;stroke-width:var(--ionicon-stroke-width, 32px)}.icon-inner,.ionicon,svg{display:block;height:100%;width:100%}:host(.flip-rtl) .icon-inner{transform:scaleX(-1)}:host(.icon-small){font-size:18px !important}:host(.icon-large){font-size:32px !important}:host(.ion-color){color:var(--ion-color-base) !important}:host(.ion-color-primary){--ion-color-base:var(--ion-color-primary, #3880ff)}:host(.ion-color-secondary){--ion-color-base:var(--ion-color-secondary, #0cd1e8)}:host(.ion-color-tertiary){--ion-color-base:var(--ion-color-tertiary, #f4a942)}:host(.ion-color-success){--ion-color-base:var(--ion-color-success, #10dc60)}:host(.ion-color-warning){--ion-color-base:var(--ion-color-warning, #ffce00)}:host(.ion-color-danger){--ion-color-base:var(--ion-color-danger, #f14141)}:host(.ion-color-light){--ion-color-base:var(--ion-color-light, #f4f5f8)}:host(.ion-color-medium){--ion-color-base:var(--ion-color-medium, #989aa2)}:host(.ion-color-dark){--ion-color-base:var(--ion-color-dark, #222428)}"}}]);