/*! For license information please see 3143.bundle.js.LICENSE.txt */
"use strict";(self.webpackChunkamwal_magento_react=self.webpackChunkamwal_magento_react||[]).push([[3143],{8248:function(t,e,n){n.d(e,{a:function(){return u},b:function(){return f},c:function(){return o},d:function(){return a},e:function(){return v},f:function(){return p},g:function(){return h},h:function(){return m},i:function(){return c},j:function(){return y},k:function(){return T},l:function(){return b},m:function(){return x},n:function(){return w},o:function(){return s},p:function(){return g},q:function(){return L},r:function(){return d},s:function(){return S},t:function(){return i},u:function(){return _},v:function(){return E}});const i=(t,e=0)=>new Promise((n=>{r(t,e,n)})),r=(t,e=0,n)=>{let i,r;const o={passive:!0},s=()=>{i&&i()},a=e=>{void 0!==e&&t!==e.target||(s(),n(e))};return t&&(t.addEventListener("webkitTransitionEnd",a,o),t.addEventListener("transitionend",a,o),r=setTimeout(a,e+500),i=()=>{r&&(clearTimeout(r),r=void 0),t.removeEventListener("webkitTransitionEnd",a,o),t.removeEventListener("transitionend",a,o)}),s},o=(t,e)=>{t.componentOnReady?t.componentOnReady().then((t=>e(t))):f((()=>e(t)))},s=t=>void 0!==t.componentOnReady,a=(t,e=[])=>{const n={};return e.forEach((e=>{t.hasAttribute(e)&&(null!==t.getAttribute(e)&&(n[e]=t.getAttribute(e)),t.removeAttribute(e))})),n},l=["role","aria-activedescendant","aria-atomic","aria-autocomplete","aria-braillelabel","aria-brailleroledescription","aria-busy","aria-checked","aria-colcount","aria-colindex","aria-colindextext","aria-colspan","aria-controls","aria-current","aria-describedby","aria-description","aria-details","aria-disabled","aria-errormessage","aria-expanded","aria-flowto","aria-haspopup","aria-hidden","aria-invalid","aria-keyshortcuts","aria-label","aria-labelledby","aria-level","aria-live","aria-multiline","aria-multiselectable","aria-orientation","aria-owns","aria-placeholder","aria-posinset","aria-pressed","aria-readonly","aria-relevant","aria-required","aria-roledescription","aria-rowcount","aria-rowindex","aria-rowindextext","aria-rowspan","aria-selected","aria-setsize","aria-sort","aria-valuemax","aria-valuemin","aria-valuenow","aria-valuetext"],c=(t,e)=>{let n=l;return e&&e.length>0&&(n=n.filter((t=>!e.includes(t)))),a(t,n)},u=(t,e,n,i)=>{var r;if("undefined"!=typeof window){const o=window,s=null===(r=null==o?void 0:o.Ionic)||void 0===r?void 0:r.config;if(s){const r=s.get("_ael");if(r)return r(t,e,n,i);if(s._ael)return s._ael(t,e,n,i)}}return t.addEventListener(e,n,i)},d=(t,e,n,i)=>{var r;if("undefined"!=typeof window){const o=window,s=null===(r=null==o?void 0:o.Ionic)||void 0===r?void 0:r.config;if(s){const r=s.get("_rel");if(r)return r(t,e,n,i);if(s._rel)return s._rel(t,e,n,i)}}return t.removeEventListener(e,n,i)},h=(t,e=t)=>t.shadowRoot||e,f=t=>"function"==typeof __zone_symbol__requestAnimationFrame?__zone_symbol__requestAnimationFrame(t):"function"==typeof requestAnimationFrame?requestAnimationFrame(t):setTimeout(t),m=t=>!!t.shadowRoot&&!!t.attachShadow,b=t=>{const e=t.closest("ion-item");return e?e.querySelector("ion-label"):null},p=t=>{if(t.focus(),t.classList.contains("ion-focusable")){const e=t.closest("ion-app");e&&e.setFocus([t])}},g=(t,e)=>{let n;const i=t.getAttribute("aria-labelledby"),r=t.id;let o=null!==i&&""!==i.trim()?i:e+"-lbl",s=null!==i&&""!==i.trim()?document.getElementById(i):b(t);return s?(null===i&&(s.id=o),n=s.textContent,s.setAttribute("aria-hidden","true")):""!==r.trim()&&(s=document.querySelector(`label[for="${r}"]`),s&&(""!==s.id?o=s.id:s.id=o=`${r}-lbl`,n=s.textContent)),{label:s,labelId:o,labelText:n}},v=(t,e,n,i,r)=>{if(t||m(e)){let t=e.querySelector("input.aux-input");t||(t=e.ownerDocument.createElement("input"),t.type="hidden",t.classList.add("aux-input"),e.appendChild(t)),t.disabled=r,t.name=n,t.value=i||""}},y=(t,e,n)=>Math.max(t,Math.min(e,n)),w=(t,e)=>{if(!t){const t="ASSERT: "+e;throw console.error(t),new Error(t)}},E=t=>t.timeStamp||Date.now(),_=t=>{if(t){const e=t.changedTouches;if(e&&e.length>0){const t=e[0];return{x:t.clientX,y:t.clientY}}if(void 0!==t.pageX)return{x:t.pageX,y:t.pageY}}return{x:0,y:0}},x=t=>{const e="rtl"===document.dir;switch(t){case"start":return e;case"end":return!e;default:throw new Error(`"${t}" is not a valid value for [side]. Use "start" or "end" instead.`)}},T=(t,e)=>{const n=t._original||t;return{_original:t,emit:L(n.emit.bind(n),e)}},L=(t,e=0)=>{let n;return(...i)=>{clearTimeout(n),n=setTimeout(t,e,...i)}},S=(t,e)=>{if(null!=t||(t={}),null!=e||(e={}),t===e)return!0;const n=Object.keys(t);if(n.length!==Object.keys(e).length)return!1;for(const i of n){if(!(i in e))return!1;if(t[i]!==e[i])return!1}return!0}},1983:function(t,e,n){n.d(e,{a:function(){return r},b:function(){return o},p:function(){return i}});const i=(t,...e)=>console.warn(`[Ionic Warning]: ${t}`,...e),r=(t,...e)=>console.error(`[Ionic Error]: ${t}`,...e),o=(t,...e)=>console.error(`<${t.tagName.toLowerCase()}> must be used inside ${e.join(" or ")}.`)},6378:function(t,e,n){n.d(e,{I:function(){return s},a:function(){return d},b:function(){return o},c:function(){return f},d:function(){return b},f:function(){return u},g:function(){return c},i:function(){return l},p:function(){return m},r:function(){return p},s:function(){return h}});var i=n(8248),r=n(1983);const o="ion-content",s=".ion-content-scroll-host",a=`${o}, ${s}`,l=t=>"ION-CONTENT"===t.tagName,c=async t=>l(t)?(await new Promise((e=>(0,i.c)(t,e))),t.getScrollElement()):t,u=t=>t.querySelector(s)||t.querySelector(a),d=t=>t.closest(a),h=(t,e)=>l(t)?t.scrollToTop(e):Promise.resolve(t.scrollTo({top:0,left:0,behavior:e>0?"smooth":"auto"})),f=(t,e,n,i)=>l(t)?t.scrollByPoint(e,n,i):Promise.resolve(t.scrollBy({top:n,left:e,behavior:i>0?"smooth":"auto"})),m=t=>(0,r.b)(t,o),b=t=>{if(l(t)){const e=t,n=e.scrollY;return e.scrollY=!1,n}return t.style.setProperty("overflow","hidden"),!0},p=(t,e)=>{l(t)?t.scrollY=e:t.style.removeProperty("overflow")}},3143:function(t,e,n){n.r(e),n.d(e,{ion_infinite_scroll:function(){return s}});var i=n(2170),r=n(399),o=n(6378);const s=class{constructor(t){(0,i.r)(this,t),this.ionInfinite=(0,i.d)(this,"ionInfinite",7),this.thrPx=0,this.thrPc=0,this.didFire=!1,this.isBusy=!1,this.onScroll=()=>{const t=this.scrollEl;if(!t||!this.canStart())return 1;const e=this.el.offsetHeight;if(0===e)return 2;const n=t.scrollTop,i=t.scrollHeight,r=t.offsetHeight,o=0!==this.thrPc?r*this.thrPc:this.thrPx;if(("bottom"===this.position?i-e-n-o-r:n-e-o)<0){if(!this.didFire)return this.isLoading=!0,this.didFire=!0,this.ionInfinite.emit(),3}else this.didFire=!1;return 4},this.isLoading=!1,this.threshold="15%",this.disabled=!1,this.position="bottom"}thresholdChanged(){const t=this.threshold;t.lastIndexOf("%")>-1?(this.thrPx=0,this.thrPc=parseFloat(t)/100):(this.thrPx=parseFloat(t),this.thrPc=0)}disabledChanged(){const t=this.disabled;t&&(this.isLoading=!1,this.isBusy=!1),this.enableScrollEvents(!t)}async connectedCallback(){const t=(0,o.a)(this.el);t?(this.scrollEl=await(0,o.g)(t),this.thresholdChanged(),this.disabledChanged(),"top"===this.position&&(0,i.w)((()=>{this.scrollEl&&(this.scrollEl.scrollTop=this.scrollEl.scrollHeight-this.scrollEl.clientHeight)}))):(0,o.p)(this.el)}disconnectedCallback(){this.enableScrollEvents(!1),this.scrollEl=void 0}async complete(){const t=this.scrollEl;if(this.isLoading&&t&&(this.isLoading=!1,"top"===this.position)){this.isBusy=!0;const e=t.scrollHeight-t.scrollTop;requestAnimationFrame((()=>{(0,i.f)((()=>{const n=t.scrollHeight-e;requestAnimationFrame((()=>{(0,i.w)((()=>{t.scrollTop=n,this.isBusy=!1}))}))}))}))}}canStart(){return!(this.disabled||this.isBusy||!this.scrollEl||this.isLoading)}enableScrollEvents(t){this.scrollEl&&(t?this.scrollEl.addEventListener("scroll",this.onScroll):this.scrollEl.removeEventListener("scroll",this.onScroll))}render(){const t=(0,r.g)(this),e=this.disabled;return(0,i.h)(i.H,{class:{[t]:!0,"infinite-scroll-loading":this.isLoading,"infinite-scroll-enabled":!e}})}get el(){return(0,i.e)(this)}static get watchers(){return{threshold:["thresholdChanged"],disabled:["disabledChanged"]}}};s.style="ion-infinite-scroll{display:none;width:100%}.infinite-scroll-enabled{display:block}"}}]);