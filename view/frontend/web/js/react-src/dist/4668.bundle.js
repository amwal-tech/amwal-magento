/*! For license information please see 4668.bundle.js.LICENSE.txt */
"use strict";(self.webpackChunkamwal_magento_react=self.webpackChunkamwal_magento_react||[]).push([[4668],{614:function(e,t,n){n.d(t,{a:function(){return d},b:function(){return h},c:function(){return i},d:function(){return l},e:function(){return v},f:function(){return y},g:function(){return p},h:function(){return b},i:function(){return c},j:function(){return g},k:function(){return S},l:function(){return f},m:function(){return k},n:function(){return w},o:function(){return a},p:function(){return m},q:function(){return C},r:function(){return u},s:function(){return A},t:function(){return o},u:function(){return x},v:function(){return E}});const o=(e,t=0)=>new Promise((n=>{r(e,t,n)})),r=(e,t=0,n)=>{let o,r;const i={passive:!0},a=()=>{o&&o()},l=t=>{void 0!==t&&e!==t.target||(a(),n(t))};return e&&(e.addEventListener("webkitTransitionEnd",l,i),e.addEventListener("transitionend",l,i),r=setTimeout(l,t+500),o=()=>{r&&(clearTimeout(r),r=void 0),e.removeEventListener("webkitTransitionEnd",l,i),e.removeEventListener("transitionend",l,i)}),a},i=(e,t)=>{e.componentOnReady?e.componentOnReady().then((e=>t(e))):h((()=>t(e)))},a=e=>void 0!==e.componentOnReady,l=(e,t=[])=>{const n={};return t.forEach((t=>{e.hasAttribute(t)&&(null!==e.getAttribute(t)&&(n[t]=e.getAttribute(t)),e.removeAttribute(t))})),n},s=["role","aria-activedescendant","aria-atomic","aria-autocomplete","aria-braillelabel","aria-brailleroledescription","aria-busy","aria-checked","aria-colcount","aria-colindex","aria-colindextext","aria-colspan","aria-controls","aria-current","aria-describedby","aria-description","aria-details","aria-disabled","aria-errormessage","aria-expanded","aria-flowto","aria-haspopup","aria-hidden","aria-invalid","aria-keyshortcuts","aria-label","aria-labelledby","aria-level","aria-live","aria-multiline","aria-multiselectable","aria-orientation","aria-owns","aria-placeholder","aria-posinset","aria-pressed","aria-readonly","aria-relevant","aria-required","aria-roledescription","aria-rowcount","aria-rowindex","aria-rowindextext","aria-rowspan","aria-selected","aria-setsize","aria-sort","aria-valuemax","aria-valuemin","aria-valuenow","aria-valuetext"],c=(e,t)=>{let n=s;return t&&t.length>0&&(n=n.filter((e=>!t.includes(e)))),l(e,n)},d=(e,t,n,o)=>{var r;if("undefined"!=typeof window){const i=window,a=null===(r=null==i?void 0:i.Ionic)||void 0===r?void 0:r.config;if(a){const r=a.get("_ael");if(r)return r(e,t,n,o);if(a._ael)return a._ael(e,t,n,o)}}return e.addEventListener(t,n,o)},u=(e,t,n,o)=>{var r;if("undefined"!=typeof window){const i=window,a=null===(r=null==i?void 0:i.Ionic)||void 0===r?void 0:r.config;if(a){const r=a.get("_rel");if(r)return r(e,t,n,o);if(a._rel)return a._rel(e,t,n,o)}}return e.removeEventListener(t,n,o)},p=(e,t=e)=>e.shadowRoot||t,h=e=>"function"==typeof __zone_symbol__requestAnimationFrame?__zone_symbol__requestAnimationFrame(e):"function"==typeof requestAnimationFrame?requestAnimationFrame(e):setTimeout(e),b=e=>!!e.shadowRoot&&!!e.attachShadow,f=e=>{const t=e.closest("ion-item");return t?t.querySelector("ion-label"):null},y=e=>{if(e.focus(),e.classList.contains("ion-focusable")){const t=e.closest("ion-app");t&&t.setFocus([e])}},m=(e,t)=>{let n;const o=e.getAttribute("aria-labelledby"),r=e.id;let i=null!==o&&""!==o.trim()?o:t+"-lbl",a=null!==o&&""!==o.trim()?document.getElementById(o):f(e);return a?(null===o&&(a.id=i),n=a.textContent,a.setAttribute("aria-hidden","true")):""!==r.trim()&&(a=document.querySelector(`label[for="${r}"]`),a&&(""!==a.id?i=a.id:a.id=i=`${r}-lbl`,n=a.textContent)),{label:a,labelId:i,labelText:n}},v=(e,t,n,o,r)=>{if(e||b(t)){let e=t.querySelector("input.aux-input");e||(e=t.ownerDocument.createElement("input"),e.type="hidden",e.classList.add("aux-input"),t.appendChild(e)),e.disabled=r,e.name=n,e.value=o||""}},g=(e,t,n)=>Math.max(e,Math.min(t,n)),w=(e,t)=>{if(!e){const e="ASSERT: "+t;throw console.error(e),new Error(e)}},x=e=>e.timeStamp||Date.now(),E=e=>{if(e){const t=e.changedTouches;if(t&&t.length>0){const e=t[0];return{x:e.clientX,y:e.clientY}}if(void 0!==e.pageX)return{x:e.pageX,y:e.pageY}}return{x:0,y:0}},k=e=>{const t="rtl"===document.dir;switch(e){case"start":return t;case"end":return!t;default:throw new Error(`"${e}" is not a valid value for [side]. Use "start" or "end" instead.`)}},S=(e,t)=>{const n=e._original||e;return{_original:e,emit:C(n.emit.bind(n),t)}},C=(e,t=0)=>{let n;return(...o)=>{clearTimeout(n),n=setTimeout(e,t,...o)}},A=(e,t)=>{if(null!=e||(e={}),null!=t||(t={}),e===t)return!0;const n=Object.keys(e);if(n.length!==Object.keys(t).length)return!1;for(const o of n){if(!(o in t))return!1;if(e[o]!==t[o])return!1}return!0}},1983:function(e,t,n){n.d(t,{a:function(){return r},b:function(){return i},p:function(){return o}});const o=(e,...t)=>console.warn(`[Ionic Warning]: ${e}`,...t),r=(e,...t)=>console.error(`[Ionic Error]: ${e}`,...t),i=(e,...t)=>console.error(`<${e.tagName.toLowerCase()}> must be used inside ${t.join(" or ")}.`)},4511:function(e,t,n){n.d(t,{I:function(){return a},a:function(){return u},b:function(){return i},c:function(){return h},d:function(){return f},f:function(){return d},g:function(){return c},i:function(){return s},p:function(){return b},r:function(){return y},s:function(){return p}});var o=n(614),r=n(1983);const i="ion-content",a=".ion-content-scroll-host",l=`${i}, ${a}`,s=e=>"ION-CONTENT"===e.tagName,c=async e=>s(e)?(await new Promise((t=>(0,o.c)(e,t))),e.getScrollElement()):e,d=e=>e.querySelector(a)||e.querySelector(l),u=e=>e.closest(l),p=(e,t)=>s(e)?e.scrollToTop(t):Promise.resolve(e.scrollTo({top:0,left:0,behavior:t>0?"smooth":"auto"})),h=(e,t,n,o)=>s(e)?e.scrollByPoint(t,n,o):Promise.resolve(e.scrollBy({top:n,left:t,behavior:o>0?"smooth":"auto"})),b=e=>(0,r.b)(e,i),f=e=>{if(s(e)){const t=e,n=t.scrollY;return t.scrollY=!1,n}return e.style.setProperty("overflow","hidden"),!0},y=(e,t)=>{s(e)?e.scrollY=t:e.style.removeProperty("overflow")}},4668:function(e,t,n){n.r(t),n.d(t,{ion_header:function(){return h}});var o=n(3542),r=n(4511),i=n(614),a=n(601),l=n(1209);const s=e=>{const t=document.querySelector(`${e}.ion-cloned-element`);if(null!==t)return t;const n=document.createElement(e);return n.classList.add("ion-cloned-element"),n.style.setProperty("display","none"),document.body.appendChild(n),n},c=e=>{if(!e)return;const t=e.querySelectorAll("ion-toolbar");return{el:e,toolbars:Array.from(t).map((e=>{const t=e.querySelector("ion-title");return{el:e,background:e.shadowRoot.querySelector(".toolbar-background"),ionTitleEl:t,innerTitleEl:t?t.shadowRoot.querySelector(".toolbar-title"):null,ionButtonsEl:Array.from(e.querySelectorAll("ion-buttons"))}}))}},d=(e,t)=>{"fade"!==e.collapse&&(void 0===t?e.style.removeProperty("--opacity-scale"):e.style.setProperty("--opacity-scale",t.toString()))},u=(e,t=!0)=>{const n=e.el;t?(n.classList.remove("header-collapse-condense-inactive"),n.removeAttribute("aria-hidden")):(n.classList.add("header-collapse-condense-inactive"),n.setAttribute("aria-hidden","true"))},p=(e,t,n)=>{(0,o.f)((()=>{const r=e.scrollTop,a=t.clientHeight,l=n?n.clientHeight:0;if(null!==n&&r<l)return t.style.setProperty("--opacity-scale","0"),void e.style.setProperty("clip-path",`inset(${a}px 0px 0px 0px)`);const s=r-l,c=(0,i.j)(0,s/10,1);(0,o.w)((()=>{e.style.removeProperty("clip-path"),t.style.setProperty("--opacity-scale",c.toString())}))}))},h=class{constructor(e){(0,o.r)(this,e),this.inheritedAttributes={},this.setupFadeHeader=async(e,t)=>{const n=this.scrollEl=await(0,r.g)(e);this.contentScrollCallback=()=>{p(this.scrollEl,this.el,t)},n.addEventListener("scroll",this.contentScrollCallback),p(this.scrollEl,this.el,t)},this.collapse=void 0,this.translucent=!1}componentWillLoad(){this.inheritedAttributes=(0,i.i)(this.el)}componentDidLoad(){this.checkCollapsibleHeader()}componentDidUpdate(){this.checkCollapsibleHeader()}disconnectedCallback(){this.destroyCollapsibleHeader()}async checkCollapsibleHeader(){if("ios"!==(0,l.g)(this))return;const{collapse:e}=this,t="condense"===e,n="fade"===e;if(this.destroyCollapsibleHeader(),t){const e=this.el.closest("ion-app,ion-page,.ion-page,page-inner"),t=e?(0,r.f)(e):null;(0,o.w)((()=>{s("ion-title").size="large",s("ion-back-button")})),await this.setupCondenseHeader(t,e)}else if(n){const e=this.el.closest("ion-app,ion-page,.ion-page,page-inner"),t=e?(0,r.f)(e):null;if(!t)return void(0,r.p)(this.el);const n=t.querySelector('ion-header[collapse="condense"]');await this.setupFadeHeader(t,n)}}destroyCollapsibleHeader(){this.intersectionObserver&&(this.intersectionObserver.disconnect(),this.intersectionObserver=void 0),this.scrollEl&&this.contentScrollCallback&&(this.scrollEl.removeEventListener("scroll",this.contentScrollCallback),this.contentScrollCallback=void 0),this.collapsibleMainHeader&&(this.collapsibleMainHeader.classList.remove("header-collapse-main"),this.collapsibleMainHeader=void 0)}async setupCondenseHeader(e,t){if(!e||!t)return void(0,r.p)(this.el);if("undefined"==typeof IntersectionObserver)return;this.scrollEl=await(0,r.g)(e);const n=t.querySelectorAll("ion-header");if(this.collapsibleMainHeader=Array.from(n).find((e=>"condense"!==e.collapse)),!this.collapsibleMainHeader)return;const a=c(this.collapsibleMainHeader),l=c(this.el);a&&l&&(u(a,!1),d(a.el,0),this.intersectionObserver=new IntersectionObserver((e=>{((e,t,n,r)=>{(0,o.w)((()=>{const o=r.scrollTop;((e,t,n)=>{if(!e[0].isIntersecting)return;const o=e[0].intersectionRatio>.9||n<=0?0:100*(1-e[0].intersectionRatio)/75;d(t.el,1===o?void 0:o)})(e,t,o);const i=e[0],a=i.intersectionRect,l=a.width*a.height,s=i.rootBounds.width*i.rootBounds.height,c=0===l&&0===s,p=Math.abs(a.left-i.boundingClientRect.left),h=Math.abs(a.right-i.boundingClientRect.right);c||l>0&&(p>=5||h>=5)||(i.isIntersecting?(u(t,!1),u(n)):(0===a.x&&0===a.y||0!==a.width&&0!==a.height)&&o>0&&(u(t),u(n,!1),d(t.el)))}))})(e,a,l,this.scrollEl)}),{root:e,threshold:[.25,.3,.4,.5,.6,.7,.8,.9,1]}),this.intersectionObserver.observe(l.toolbars[l.toolbars.length-1].el),this.contentScrollCallback=()=>{((e,t,n)=>{(0,o.f)((()=>{const r=e.scrollTop,a=(0,i.j)(1,1+-r/500,1.1);null===n.querySelector("ion-refresher.refresher-native")&&(0,o.w)((()=>{((e=[],t=1,n=!1)=>{e.forEach((e=>{const o=e.ionTitleEl,r=e.innerTitleEl;o&&"large"===o.size&&(r.style.transition=n?"all 0.2s ease-in-out":"",r.style.transform=`scale3d(${t}, ${t}, 1)`)}))})(t.toolbars,a)}))}))})(this.scrollEl,l,e)},this.scrollEl.addEventListener("scroll",this.contentScrollCallback),(0,o.w)((()=>{void 0!==this.collapsibleMainHeader&&this.collapsibleMainHeader.classList.add("header-collapse-main")})))}render(){const{translucent:e,inheritedAttributes:t}=this,n=(0,l.g)(this),r=this.collapse||"none",i=(0,a.h)("ion-menu",this.el)?"none":"banner";return(0,o.h)(o.H,Object.assign({role:i,class:{[n]:!0,[`header-${n}`]:!0,"header-translucent":this.translucent,[`header-collapse-${r}`]:!0,[`header-translucent-${n}`]:this.translucent}},t),"ios"===n&&e&&(0,o.h)("div",{class:"header-background"}),(0,o.h)("slot",null))}get el(){return(0,o.e)(this)}};h.style={ios:"ion-header{display:block;position:relative;order:-1;width:100%;z-index:10}ion-header ion-toolbar:first-of-type{padding-top:var(--ion-safe-area-top, 0)}.header-ios ion-toolbar:last-of-type{--border-width:0 0 0.55px}@supports (backdrop-filter: blur(0)){.header-background{left:0;right:0;top:0;bottom:0;position:absolute;backdrop-filter:saturate(180%) blur(20px)}.header-translucent-ios ion-toolbar{--opacity:.8}.header-collapse-condense-inactive .header-background{backdrop-filter:blur(20px)}}.header-ios.ion-no-border ion-toolbar:last-of-type{--border-width:0}.header-collapse-fade ion-toolbar{--opacity-scale:inherit}.header-collapse-condense{z-index:9}.header-collapse-condense ion-toolbar{position:sticky;top:0}.header-collapse-condense ion-toolbar:first-of-type{padding-top:1px;z-index:1}.header-collapse-condense ion-toolbar{--background:var(--ion-background-color, #fff);z-index:0}.header-collapse-condense ion-toolbar:last-of-type{--border-width:0px}.header-collapse-condense ion-toolbar ion-searchbar{height:48px;padding-top:0px;padding-bottom:13px}.header-collapse-main{--opacity-scale:1}.header-collapse-main ion-toolbar{--opacity-scale:inherit}.header-collapse-main ion-toolbar.in-toolbar ion-title,.header-collapse-main ion-toolbar.in-toolbar ion-buttons{transition:all 0.2s ease-in-out}.header-collapse-condense-inactive:not(.header-collapse-condense) ion-toolbar.in-toolbar ion-title,.header-collapse-condense-inactive:not(.header-collapse-condense) ion-toolbar.in-toolbar ion-buttons.buttons-collapse{opacity:0;pointer-events:none}.header-collapse-condense-inactive.header-collapse-condense ion-toolbar.in-toolbar ion-title,.header-collapse-condense-inactive.header-collapse-condense ion-toolbar.in-toolbar ion-buttons.buttons-collapse{visibility:hidden}",md:"ion-header{display:block;position:relative;order:-1;width:100%;z-index:10}ion-header ion-toolbar:first-of-type{padding-top:var(--ion-safe-area-top, 0)}.header-md{box-shadow:0 2px 4px -1px rgba(0, 0, 0, 0.2), 0 4px 5px 0 rgba(0, 0, 0, 0.14), 0 1px 10px 0 rgba(0, 0, 0, 0.12)}.header-collapse-condense{display:none}.header-md.ion-no-border{box-shadow:none}"}},601:function(e,t,n){n.d(t,{c:function(){return r},g:function(){return i},h:function(){return o},o:function(){return l}});const o=(e,t)=>null!==t.closest(e),r=(e,t)=>"string"==typeof e&&e.length>0?Object.assign({"ion-color":!0,[`ion-color-${e}`]:!0},t):t,i=e=>{const t={};return(e=>void 0!==e?(Array.isArray(e)?e:e.split(" ")).filter((e=>null!=e)).map((e=>e.trim())).filter((e=>""!==e)):[])(e).forEach((e=>t[e]=!0)),t},a=/^[a-z][a-z0-9+\-.]*:/,l=async(e,t,n,o)=>{if(null!=e&&"#"!==e[0]&&!a.test(e)){const r=document.querySelector("ion-router");if(r)return null!=t&&t.preventDefault(),r.push(e,n,o)}return!1}}}]);