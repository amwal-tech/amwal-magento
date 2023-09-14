/*! For license information please see 9963.bundle.js.LICENSE.txt */
"use strict";(self.webpackChunkamwal_magento_react=self.webpackChunkamwal_magento_react||[]).push([[9963,3617],{3617:function(e,t,n){n.r(t),n.d(t,{MENU_BACK_BUTTON_PRIORITY:function(){return o},OVERLAY_BACK_BUTTON_PRIORITY:function(){return a},blockHardwareBackButton:function(){return r},startHardwareBackButton:function(){return i}});const r=()=>{document.addEventListener("backbutton",(()=>{}))},i=()=>{const e=document;let t=!1;e.addEventListener("backbutton",(()=>{if(t)return;let n=0,r=[];const i=new CustomEvent("ionBackButton",{bubbles:!1,detail:{register(e,t){r.push({priority:e,handler:t,id:n++})}}});e.dispatchEvent(i);const a=()=>{if(r.length>0){let e={priority:Number.MIN_SAFE_INTEGER,handler:()=>{},id:-1};r.forEach((t=>{t.priority>=e.priority&&(e=t)})),t=!0,r=r.filter((t=>t.id!==e.id)),(async e=>{try{if(null==e?void 0:e.handler){const t=e.handler(a);null!=t&&await t}}catch(e){console.error(e)}})(e).then((()=>t=!1))}};a()}))},a=100,o=99},614:function(e,t,n){n.d(t,{a:function(){return c},b:function(){return m},c:function(){return a},d:function(){return s},e:function(){return y},f:function(){return b},g:function(){return f},h:function(){return p},i:function(){return l},j:function(){return h},k:function(){return A},l:function(){return w},m:function(){return x},n:function(){return g},o:function(){return o},p:function(){return v},q:function(){return T},r:function(){return d},s:function(){return O},t:function(){return r},u:function(){return E},v:function(){return _}});const r=(e,t=0)=>new Promise((n=>{i(e,t,n)})),i=(e,t=0,n)=>{let r,i;const a={passive:!0},o=()=>{r&&r()},s=t=>{void 0!==t&&e!==t.target||(o(),n(t))};return e&&(e.addEventListener("webkitTransitionEnd",s,a),e.addEventListener("transitionend",s,a),i=setTimeout(s,t+500),r=()=>{i&&(clearTimeout(i),i=void 0),e.removeEventListener("webkitTransitionEnd",s,a),e.removeEventListener("transitionend",s,a)}),o},a=(e,t)=>{e.componentOnReady?e.componentOnReady().then((e=>t(e))):m((()=>t(e)))},o=e=>void 0!==e.componentOnReady,s=(e,t=[])=>{const n={};return t.forEach((t=>{e.hasAttribute(t)&&(null!==e.getAttribute(t)&&(n[t]=e.getAttribute(t)),e.removeAttribute(t))})),n},u=["role","aria-activedescendant","aria-atomic","aria-autocomplete","aria-braillelabel","aria-brailleroledescription","aria-busy","aria-checked","aria-colcount","aria-colindex","aria-colindextext","aria-colspan","aria-controls","aria-current","aria-describedby","aria-description","aria-details","aria-disabled","aria-errormessage","aria-expanded","aria-flowto","aria-haspopup","aria-hidden","aria-invalid","aria-keyshortcuts","aria-label","aria-labelledby","aria-level","aria-live","aria-multiline","aria-multiselectable","aria-orientation","aria-owns","aria-placeholder","aria-posinset","aria-pressed","aria-readonly","aria-relevant","aria-required","aria-roledescription","aria-rowcount","aria-rowindex","aria-rowindextext","aria-rowspan","aria-selected","aria-setsize","aria-sort","aria-valuemax","aria-valuemin","aria-valuenow","aria-valuetext"],l=(e,t)=>{let n=u;return t&&t.length>0&&(n=n.filter((e=>!t.includes(e)))),s(e,n)},c=(e,t,n,r)=>{var i;if("undefined"!=typeof window){const a=window,o=null===(i=null==a?void 0:a.Ionic)||void 0===i?void 0:i.config;if(o){const i=o.get("_ael");if(i)return i(e,t,n,r);if(o._ael)return o._ael(e,t,n,r)}}return e.addEventListener(t,n,r)},d=(e,t,n,r)=>{var i;if("undefined"!=typeof window){const a=window,o=null===(i=null==a?void 0:a.Ionic)||void 0===i?void 0:i.config;if(o){const i=o.get("_rel");if(i)return i(e,t,n,r);if(o._rel)return o._rel(e,t,n,r)}}return e.removeEventListener(t,n,r)},f=(e,t=e)=>e.shadowRoot||t,m=e=>"function"==typeof __zone_symbol__requestAnimationFrame?__zone_symbol__requestAnimationFrame(e):"function"==typeof requestAnimationFrame?requestAnimationFrame(e):setTimeout(e),p=e=>!!e.shadowRoot&&!!e.attachShadow,w=e=>{const t=e.closest("ion-item");return t?t.querySelector("ion-label"):null},b=e=>{if(e.focus(),e.classList.contains("ion-focusable")){const t=e.closest("ion-app");t&&t.setFocus([e])}},v=(e,t)=>{let n;const r=e.getAttribute("aria-labelledby"),i=e.id;let a=null!==r&&""!==r.trim()?r:t+"-lbl",o=null!==r&&""!==r.trim()?document.getElementById(r):w(e);return o?(null===r&&(o.id=a),n=o.textContent,o.setAttribute("aria-hidden","true")):""!==i.trim()&&(o=document.querySelector(`label[for="${i}"]`),o&&(""!==o.id?a=o.id:o.id=a=`${i}-lbl`,n=o.textContent)),{label:o,labelId:a,labelText:n}},y=(e,t,n,r,i)=>{if(e||p(t)){let e=t.querySelector("input.aux-input");e||(e=t.ownerDocument.createElement("input"),e.type="hidden",e.classList.add("aux-input"),t.appendChild(e)),e.disabled=i,e.name=n,e.value=r||""}},h=(e,t,n)=>Math.max(e,Math.min(t,n)),g=(e,t)=>{if(!e){const e="ASSERT: "+t;throw console.error(e),new Error(e)}},E=e=>e.timeStamp||Date.now(),_=e=>{if(e){const t=e.changedTouches;if(t&&t.length>0){const e=t[0];return{x:e.clientX,y:e.clientY}}if(void 0!==e.pageX)return{x:e.pageX,y:e.pageY}}return{x:0,y:0}},x=e=>{const t="rtl"===document.dir;switch(e){case"start":return t;case"end":return!t;default:throw new Error(`"${e}" is not a valid value for [side]. Use "start" or "end" instead.`)}},A=(e,t)=>{const n=e._original||e;return{_original:e,emit:T(n.emit.bind(n),t)}},T=(e,t=0)=>{let n;return(...r)=>{clearTimeout(n),n=setTimeout(e,t,...r)}},O=(e,t)=>{if(null!=e||(e={}),null!=t||(t={}),e===t)return!0;const n=Object.keys(e);if(n.length!==Object.keys(t).length)return!1;for(const r of n){if(!(r in t))return!1;if(e[r]!==t[r])return!1}return!0}},6003:function(e,t,n){n.d(t,{m:function(){return d}});var r=n(3617),i=n(614),a=n(1209),o=n(7771);const s=e=>(0,o.c)().duration(e?400:300),u=e=>{let t,n;const r=e.width+8,i=(0,o.c)(),u=(0,o.c)();e.isEndSide?(t=r+"px",n="0px"):(t=-r+"px",n="0px"),i.addElement(e.menuInnerEl).fromTo("transform",`translateX(${t})`,`translateX(${n})`);const l="ios"===(0,a.g)(e),c=l?.2:.25;return u.addElement(e.backdropEl).fromTo("opacity",.01,c),s(l).addAnimation([i,u])},l=e=>{let t,n;const r=(0,a.g)(e),i=e.width;e.isEndSide?(t=-i+"px",n=i+"px"):(t=i+"px",n=-i+"px");const u=(0,o.c)().addElement(e.menuInnerEl).fromTo("transform",`translateX(${n})`,"translateX(0px)"),l=(0,o.c)().addElement(e.contentEl).fromTo("transform","translateX(0px)",`translateX(${t})`),c=(0,o.c)().addElement(e.backdropEl).fromTo("opacity",.01,.32);return s("ios"===r).addAnimation([u,l,c])},c=e=>{const t=(0,a.g)(e),n=e.width*(e.isEndSide?-1:1)+"px",r=(0,o.c)().addElement(e.contentEl).fromTo("transform","translateX(0px)",`translateX(${n})`);return s("ios"===t).addAnimation(r)},d=(()=>{const e=new Map,t=[],n=async e=>{if(await p(),"start"===e||"end"===e){return m((t=>t.side===e&&!t.disabled))||m((t=>t.side===e))}if(null!=e)return m((t=>t.menuId===e));return m((e=>!e.disabled))||(t.length>0?t[0].el:void 0)},a=async()=>(await p(),d()),o=(t,n)=>{e.set(t,n)},s=e=>{const n=e.side;t.filter((t=>t.side===n&&t!==e)).forEach((e=>e.disabled=!0))},d=()=>m((e=>e._isOpen)),f=()=>t.some((e=>e.isAnimating)),m=e=>{const n=t.find(e);if(void 0!==n)return n.el},p=()=>Promise.all(Array.from(document.querySelectorAll("ion-menu")).map((e=>new Promise((t=>(0,i.c)(e,t))))));return o("reveal",c),o("push",l),o("overlay",u),"undefined"!=typeof document&&document.addEventListener("ionBackButton",(e=>{const t=d();t&&e.detail.register(r.MENU_BACK_BUTTON_PRIORITY,(()=>t.close()))})),{registerAnimation:o,get:n,getMenus:async()=>(await p(),t.map((e=>e.el))),getOpen:a,isEnabled:async e=>{const t=await n(e);return!!t&&!t.disabled},swipeGesture:async(e,t)=>{const r=await n(t);return r&&(r.swipeGesture=e),r},isAnimating:async()=>(await p(),f()),isOpen:async e=>{if(null!=e){const t=await n(e);return void 0!==t&&t.isOpen()}return void 0!==await a()},enable:async(e,t)=>{const r=await n(t);return r&&(r.disabled=!e),r},toggle:async e=>{const t=await n(e);return!!t&&t.toggle()},close:async e=>{const t=await(void 0!==e?n(e):a());return void 0!==t&&t.close()},open:async e=>{const t=await n(e);return!!t&&t.open()},_getOpenSync:d,_createAnimation:(t,n)=>{const r=e.get(t);if(!r)throw new Error("animation not registered");return r(n)},_register:e=>{t.indexOf(e)<0&&(e.disabled||s(e),t.push(e))},_unregister:e=>{const n=t.indexOf(e);n>-1&&t.splice(n,1)},_setOpen:async(e,t,n)=>{if(f())return!1;if(t){const t=await a();t&&e.el!==t&&await t.setOpen(!1,!1)}return e._setOpen(t,n)},_setActiveMenu:s}})()},9963:function(e,t,n){n.d(t,{u:function(){return i}});var r=n(6003);const i=async e=>{const t=await r.m.get(e);return!(!t||!await t.isActive())}}}]);