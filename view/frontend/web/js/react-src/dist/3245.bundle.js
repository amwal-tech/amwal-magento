/*! For license information please see 3245.bundle.js.LICENSE.txt */
"use strict";(self.webpackChunkamwal_magento_react=self.webpackChunkamwal_magento_react||[]).push([[3245],{7993:function(e,t,o){o.d(t,{L:function(){return a},a:function(){return n},b:function(){return s},d:function(){return k},g:function(){return D},l:function(){return b},s:function(){return P},t:function(){return p},w:function(){return x}});var r=o(3542),i=o(614);const n="ionViewWillLeave",s="ionViewDidLeave",a="ionViewWillUnload",p=e=>new Promise(((t,o)=>{(0,r.w)((()=>{l(e),d(e).then((o=>{o.animation&&o.animation.destroy(),c(e),t(o)}),(t=>{c(e),o(t)}))}))})),l=e=>{const t=e.enteringEl,o=e.leavingEl;E(t,o,e.direction),e.showGoBack?t.classList.add("can-go-back"):t.classList.remove("can-go-back"),P(t,!1),t.style.setProperty("pointer-events","none"),o&&(P(o,!1),o.style.setProperty("pointer-events","none"))},d=async e=>{const t=await h(e);return t&&r.B.isBrowser?v(t,e):g(e)},c=e=>{const t=e.enteringEl,o=e.leavingEl;t.classList.remove("ion-page-invisible"),t.style.removeProperty("pointer-events"),void 0!==o&&(o.classList.remove("ion-page-invisible"),o.style.removeProperty("pointer-events"))},h=async e=>{if(e.leavingEl&&e.animated&&0!==e.duration)return e.animationBuilder?e.animationBuilder:"ios"===e.mode?(await Promise.all([o.e(7771),o.e(2422)]).then(o.bind(o,2422))).iosTransitionAnimation:(await Promise.all([o.e(7771),o.e(1150)]).then(o.bind(o,1150))).mdTransitionAnimation},v=async(e,t)=>{await f(t,!0);const o=e(t.baseEl,t);w(t.enteringEl,t.leavingEl);const r=await m(o,t);return t.progressCallback&&t.progressCallback(void 0),r&&y(t.enteringEl,t.leavingEl),{hasCompleted:r,animation:o}},g=async e=>{const t=e.enteringEl,o=e.leavingEl;return await f(e,!1),w(t,o),y(t,o),{hasCompleted:!0}},f=async(e,t)=>{(void 0!==e.deepWait?e.deepWait:t)&&await Promise.all([k(e.enteringEl),k(e.leavingEl)]),await u(e.viewIsReady,e.enteringEl)},u=async(e,t)=>{e&&await e(t)},m=(e,t)=>{const o=t.progressCallback,r=new Promise((t=>{e.onFinish((e=>t(1===e)))}));return o?(e.progressStart(!0),o(e)):e.play(),r},w=(e,t)=>{b(t,n),b(e,"ionViewWillEnter")},y=(e,t)=>{b(e,"ionViewDidEnter"),b(t,s)},b=(e,t)=>{if(e){const o=new CustomEvent(t,{bubbles:!1,cancelable:!1});e.dispatchEvent(o)}},x=()=>new Promise((e=>(0,i.b)((()=>(0,i.b)((()=>e())))))),k=async e=>{const t=e;if(t){if(null!=t.componentOnReady){if(null!=await t.componentOnReady())return}else if(null!=t.__registerHost){const e=new Promise((e=>(0,i.b)(e)));return void await e}await Promise.all(Array.from(t.children).map(k))}},P=(e,t)=>{t?(e.setAttribute("aria-hidden","true"),e.classList.add("ion-page-hidden")):(e.hidden=!1,e.removeAttribute("aria-hidden"),e.classList.remove("ion-page-hidden"))},E=(e,t,o)=>{void 0!==e&&(e.style.zIndex="back"===o?"99":"101"),void 0!==t&&(t.style.zIndex="100")},D=e=>{if(e.classList.contains("ion-page"))return e;return e.querySelector(":scope > .ion-page, :scope > ion-nav, :scope > ion-tabs")||e}},3245:function(e,t,o){o.r(t),o.d(t,{ion_popover:function(){return L}});var r=o(3542),i=o(5659),n=o(614),s=o(1706),a=o(1983),p=o(8747),l=o(1209),d=o(601),c=o(7993),h=o(7771);o(261);const v=(e,t,o)=>{const r=t.getBoundingClientRect(),i=r.height;let n=r.width;return"cover"===e&&o&&(n=o.getBoundingClientRect().width),{contentWidth:n,contentHeight:i}},g=(e,t)=>t&&"ION-ITEM"===t.tagName?e.findIndex((e=>e===t)):-1,f=e=>{const t=(0,n.g)(e).querySelector("button");t&&(0,n.b)((()=>t.focus()))},u=(e,t,o,r,i,n,s,a,p,l,d)=>{var c;let h={top:0,left:0,width:0,height:0};if("event"===n){if(!d)return p;const e=d;h={top:e.clientY,left:e.clientX,width:1,height:1}}else{const e=d,t=l||(null===(c=null==e?void 0:e.detail)||void 0===c?void 0:c.ionShadowTarget)||(null==e?void 0:e.target);if(!t)return p;const o=t.getBoundingClientRect();h={top:o.top,left:o.left,width:o.width,height:o.height}}const v=x(s,h,t,o,r,i,e),g=k(a,s,h,t,o),f=v.top+g.top,u=v.left+g.left,{arrowTop:w,arrowLeft:y}=b(s,r,i,f,u,t,o,e),{originX:P,originY:E}=m(s,a,e);return{top:f,left:u,referenceCoordinates:h,arrowTop:w,arrowLeft:y,originX:P,originY:E}},m=(e,t,o)=>{switch(e){case"top":return{originX:w(t),originY:"bottom"};case"bottom":return{originX:w(t),originY:"top"};case"left":return{originX:"right",originY:y(t)};case"right":return{originX:"left",originY:y(t)};case"start":return{originX:o?"left":"right",originY:y(t)};case"end":return{originX:o?"right":"left",originY:y(t)}}},w=e=>{switch(e){case"start":return"left";case"center":return"center";case"end":return"right"}},y=e=>{switch(e){case"start":return"top";case"center":return"center";case"end":return"bottom"}},b=(e,t,o,r,i,n,s,a)=>{const p={arrowTop:r+s/2-t/2,arrowLeft:i+n-t/2},l={arrowTop:r+s/2-t/2,arrowLeft:i-1.5*t};switch(e){case"top":return{arrowTop:r+s,arrowLeft:i+n/2-t/2};case"bottom":return{arrowTop:r-o,arrowLeft:i+n/2-t/2};case"left":return p;case"right":return l;case"start":return a?l:p;case"end":return a?p:l;default:return{arrowTop:0,arrowLeft:0}}},x=(e,t,o,r,i,n,s)=>{const a={top:t.top,left:t.left-o-i},p={top:t.top,left:t.left+t.width+i};switch(e){case"top":return{top:t.top-r-n,left:t.left};case"right":return p;case"bottom":return{top:t.top+t.height+n,left:t.left};case"left":return a;case"start":return s?p:a;case"end":return s?a:p}},k=(e,t,o,r,i)=>{switch(e){case"center":return E(t,o,r,i);case"end":return P(t,o,r,i);default:return{top:0,left:0}}},P=(e,t,o,r)=>{switch(e){case"start":case"end":case"left":case"right":return{top:-(r-t.height),left:0};default:return{top:0,left:-(o-t.width)}}},E=(e,t,o,r)=>{switch(e){case"start":case"end":case"left":case"right":return{top:-(r/2-t.height/2),left:0};default:return{top:0,left:-(o/2-t.width/2)}}},D=(e,t,o,r,i,n,s,a,p,l,d,c,h=0,v=0,g=0)=>{let f=h;const u=v;let m,w=o,y=t,b=l,x=d,k=!1,P=!1;const E=c?c.top+c.height:n/2-a/2,D=c?c.height:0;let T=!1;return w<r+p?(w=r,k=!0,b="left"):s+r+w+p>i&&(P=!0,w=i-s-r,b="right"),E+D+a>n&&("top"===e||"bottom"===e)&&(E-a>0?(y=Math.max(12,E-a-D-(g-1)),f=y+a,x="bottom",T=!0):m=r),{top:y,left:w,bottom:m,originX:b,originY:x,checkSafeAreaLeft:k,checkSafeAreaRight:P,arrowTop:f,arrowLeft:u,addPopoverBottomClass:T}},T=(e,t)=>{var o;const{event:r,size:i,trigger:s,reference:a,side:p,align:l}=t,d=e.ownerDocument,c="rtl"===d.dir,g=d.defaultView.innerWidth,f=d.defaultView.innerHeight,m=(0,n.g)(e),w=m.querySelector(".popover-content"),y=m.querySelector(".popover-arrow"),b=s||(null===(o=null==r?void 0:r.detail)||void 0===o?void 0:o.ionShadowTarget)||(null==r?void 0:r.target),{contentWidth:x,contentHeight:k}=v(i,w,b),{arrowWidth:P,arrowHeight:E}=(e=>{if(!e)return{arrowWidth:0,arrowHeight:0};const{width:t,height:o}=e.getBoundingClientRect();return{arrowWidth:t,arrowHeight:o}})(y),T=u(c,x,k,P,E,a,p,l,{top:f/2-k/2,left:g/2-x/2,originX:c?"right":"left",originY:"top"},s,r),A="cover"===i?0:5,S="cover"===i?0:25,{originX:C,originY:L,top:I,left:W,bottom:O,checkSafeAreaLeft:q,checkSafeAreaRight:$,arrowTop:N,arrowLeft:z,addPopoverBottomClass:B}=D(p,T.top,T.left,A,g,f,x,k,S,T.originX,T.originY,T.referenceCoordinates,T.arrowTop,T.arrowLeft,E),X=(0,h.c)(),Y=(0,h.c)(),V=(0,h.c)();return Y.addElement(m.querySelector("ion-backdrop")).fromTo("opacity",.01,"var(--backdrop-opacity)").beforeStyles({"pointer-events":"none"}).afterClearStyles(["pointer-events"]),V.addElement(m.querySelector(".popover-arrow")).addElement(m.querySelector(".popover-content")).fromTo("opacity",.01,1),X.easing("ease").duration(100).beforeAddWrite((()=>{"cover"===i&&e.style.setProperty("--width",`${x}px`),B&&e.classList.add("popover-bottom"),void 0!==O&&w.style.setProperty("bottom",`${O}px`);let t=`${W}px`;if(q&&(t=`${W}px + var(--ion-safe-area-left, 0)`),$&&(t=`${W}px - var(--ion-safe-area-right, 0)`),w.style.setProperty("top",`calc(${I}px + var(--offset-y, 0))`),w.style.setProperty("left",`calc(${t} + var(--offset-x, 0))`),w.style.setProperty("transform-origin",`${L} ${C}`),null!==y){const e=T.top!==I||T.left!==W,t=((e,t=!1,o,r)=>!(!o&&!r||"top"!==e&&"bottom"!==e&&t))(p,e,r,s);t?(y.style.setProperty("top",`calc(${N}px + var(--offset-y, 0))`),y.style.setProperty("left",`calc(${z}px + var(--offset-x, 0))`)):y.style.setProperty("display","none")}})).addAnimation([Y,V])},A=e=>{const t=(0,n.g)(e),o=t.querySelector(".popover-content"),r=t.querySelector(".popover-arrow"),i=(0,h.c)(),s=(0,h.c)(),a=(0,h.c)();return s.addElement(t.querySelector("ion-backdrop")).fromTo("opacity","var(--backdrop-opacity)",0),a.addElement(t.querySelector(".popover-arrow")).addElement(t.querySelector(".popover-content")).fromTo("opacity",.99,0),i.easing("ease").afterAddWrite((()=>{e.style.removeProperty("--width"),e.classList.remove("popover-bottom"),o.style.removeProperty("top"),o.style.removeProperty("left"),o.style.removeProperty("bottom"),o.style.removeProperty("transform-origin"),r&&(r.style.removeProperty("top"),r.style.removeProperty("left"),r.style.removeProperty("display"))})).duration(300).addAnimation([s,a])},S=(e,t)=>{var o;const{event:r,size:i,trigger:s,reference:a,side:p,align:l}=t,d=e.ownerDocument,c="rtl"===d.dir,g=d.defaultView.innerWidth,f=d.defaultView.innerHeight,m=(0,n.g)(e),w=m.querySelector(".popover-content"),y=s||(null===(o=null==r?void 0:r.detail)||void 0===o?void 0:o.ionShadowTarget)||(null==r?void 0:r.target),{contentWidth:b,contentHeight:x}=v(i,w,y),k=u(c,b,x,0,0,a,p,l,{top:f/2-x/2,left:g/2-b/2,originX:c?"right":"left",originY:"top"},s,r),P="cover"===i?0:12,{originX:E,originY:T,top:A,left:S,bottom:C}=D(p,k.top,k.left,P,g,f,b,x,0,k.originX,k.originY,k.referenceCoordinates),L=(0,h.c)(),I=(0,h.c)(),W=(0,h.c)(),O=(0,h.c)(),q=(0,h.c)();return I.addElement(m.querySelector("ion-backdrop")).fromTo("opacity",.01,"var(--backdrop-opacity)").beforeStyles({"pointer-events":"none"}).afterClearStyles(["pointer-events"]),W.addElement(m.querySelector(".popover-wrapper")).duration(150).fromTo("opacity",.01,1),O.addElement(w).beforeStyles({top:`calc(${A}px + var(--offset-y, 0px))`,left:`calc(${S}px + var(--offset-x, 0px))`,"transform-origin":`${T} ${E}`}).beforeAddWrite((()=>{void 0!==C&&w.style.setProperty("bottom",`${C}px`)})).fromTo("transform","scale(0.8)","scale(1)"),q.addElement(m.querySelector(".popover-viewport")).fromTo("opacity",.01,1),L.easing("cubic-bezier(0.36,0.66,0.04,1)").duration(300).beforeAddWrite((()=>{"cover"===i&&e.style.setProperty("--width",`${b}px`),"bottom"===T&&e.classList.add("popover-bottom")})).addAnimation([I,W,O,q])},C=e=>{const t=(0,n.g)(e),o=t.querySelector(".popover-content"),r=(0,h.c)(),i=(0,h.c)(),s=(0,h.c)();return i.addElement(t.querySelector("ion-backdrop")).fromTo("opacity","var(--backdrop-opacity)",0),s.addElement(t.querySelector(".popover-wrapper")).fromTo("opacity",.99,0),r.easing("ease").afterAddWrite((()=>{e.style.removeProperty("--width"),e.classList.remove("popover-bottom"),o.style.removeProperty("top"),o.style.removeProperty("left"),o.style.removeProperty("bottom"),o.style.removeProperty("transform-origin")})).duration(150).addAnimation([i,s])},L=class{constructor(e){(0,r.r)(this,e),this.didPresent=(0,r.d)(this,"ionPopoverDidPresent",7),this.willPresent=(0,r.d)(this,"ionPopoverWillPresent",7),this.willDismiss=(0,r.d)(this,"ionPopoverWillDismiss",7),this.didDismiss=(0,r.d)(this,"ionPopoverDidDismiss",7),this.didPresentShorthand=(0,r.d)(this,"didPresent",7),this.willPresentShorthand=(0,r.d)(this,"willPresent",7),this.willDismissShorthand=(0,r.d)(this,"willDismiss",7),this.didDismissShorthand=(0,r.d)(this,"didDismiss",7),this.ionMount=(0,r.d)(this,"ionMount",7),this.parentPopover=null,this.coreDelegate=(0,i.C)(),this.lockController=(0,s.c)(),this.inline=!1,this.focusDescendantOnPresent=!1,this.onBackdropTap=()=>{this.dismiss(void 0,p.B)},this.onLifecycle=e=>{const t=this.usersElement,o=I[e.type];if(t&&o){const r=new CustomEvent(o,{bubbles:!1,cancelable:!1,detail:e.detail});t.dispatchEvent(r)}},this.configureTriggerInteraction=()=>{const{trigger:e,triggerAction:t,el:o,destroyTriggerInteraction:r}=this;if(r&&r(),void 0===e)return;const i=this.triggerEl=void 0!==e?document.getElementById(e):null;i?this.destroyTriggerInteraction=((e,t,o)=>{let r=[];switch(t){case"hover":let e;r=[{eventName:"mouseenter",callback:async t=>{t.stopPropagation(),e&&clearTimeout(e),e=setTimeout((()=>{(0,n.b)((()=>{o.presentFromTrigger(t),e=void 0}))}),100)}},{eventName:"mouseleave",callback:t=>{e&&clearTimeout(e);const r=t.relatedTarget;r&&r.closest("ion-popover")!==o&&o.dismiss(void 0,void 0,!1)}},{eventName:"click",callback:e=>e.stopPropagation()},{eventName:"ionPopoverActivateTrigger",callback:e=>o.presentFromTrigger(e,!0)}];break;case"context-menu":r=[{eventName:"contextmenu",callback:e=>{e.preventDefault(),o.presentFromTrigger(e)}},{eventName:"click",callback:e=>e.stopPropagation()},{eventName:"ionPopoverActivateTrigger",callback:e=>o.presentFromTrigger(e,!0)}];break;default:r=[{eventName:"click",callback:e=>o.presentFromTrigger(e)},{eventName:"ionPopoverActivateTrigger",callback:e=>o.presentFromTrigger(e,!0)}]}return r.forEach((({eventName:t,callback:o})=>e.addEventListener(t,o))),e.setAttribute("data-ion-popover-trigger","true"),()=>{r.forEach((({eventName:t,callback:o})=>e.removeEventListener(t,o))),e.removeAttribute("data-ion-popover-trigger")}})(i,t,o):(0,a.p)(`A trigger element with the ID "${e}" was not found in the DOM. The trigger element must be in the DOM when the "trigger" property is set on ion-popover.`,this.el)},this.configureKeyboardInteraction=()=>{const{destroyKeyboardInteraction:e,el:t}=this;e&&e(),this.destroyKeyboardInteraction=(e=>{const t=async t=>{var o;const r=document.activeElement;let i=[];const n=null===(o=t.target)||void 0===o?void 0:o.tagName;if("ION-POPOVER"===n||"ION-ITEM"===n){try{i=Array.from(e.querySelectorAll("ion-item:not(ion-popover ion-popover *):not([disabled])"))}catch(e){}switch(t.key){case"ArrowLeft":await e.getParentPopover()&&e.dismiss(void 0,void 0,!1);break;case"ArrowDown":t.preventDefault();const o=((e,t)=>e[g(e,t)+1])(i,r);void 0!==o&&f(o);break;case"ArrowUp":t.preventDefault();const n=((e,t)=>e[g(e,t)-1])(i,r);void 0!==n&&f(n);break;case"Home":t.preventDefault();const s=i[0];void 0!==s&&f(s);break;case"End":t.preventDefault();const a=i[i.length-1];void 0!==a&&f(a);break;case"ArrowRight":case" ":case"Enter":if(r&&r.hasAttribute("data-ion-popover-trigger")){const e=new CustomEvent("ionPopoverActivateTrigger");r.dispatchEvent(e)}}}};return e.addEventListener("keydown",t),()=>e.removeEventListener("keydown",t)})(t)},this.configureDismissInteraction=()=>{const{destroyDismissInteraction:e,parentPopover:t,triggerAction:o,triggerEl:r,el:i}=this;t&&r&&(e&&e(),this.destroyDismissInteraction=((e,t,o,r)=>{let i=[];const s=(0,n.g)(r).querySelector(".popover-content");return i="hover"===t?[{eventName:"mouseenter",callback:t=>{document.elementFromPoint(t.clientX,t.clientY)!==e&&o.dismiss(void 0,void 0,!1)}}]:[{eventName:"click",callback:t=>{t.target.closest("[data-ion-popover-trigger]")!==e?o.dismiss(void 0,void 0,!1):t.stopPropagation()}}],i.forEach((({eventName:e,callback:t})=>s.addEventListener(e,t))),()=>{i.forEach((({eventName:e,callback:t})=>s.removeEventListener(e,t)))}})(r,o,i,t))},this.presented=!1,this.hasController=!1,this.delegate=void 0,this.overlayIndex=void 0,this.enterAnimation=void 0,this.leaveAnimation=void 0,this.component=void 0,this.componentProps=void 0,this.keyboardClose=!0,this.cssClass=void 0,this.backdropDismiss=!0,this.event=void 0,this.showBackdrop=!0,this.translucent=!1,this.animated=!0,this.htmlAttributes=void 0,this.triggerAction="click",this.trigger=void 0,this.size="auto",this.dismissOnSelect=!1,this.reference="trigger",this.side="bottom",this.alignment=void 0,this.arrow=!0,this.isOpen=!1,this.keyboardEvents=!1,this.keepContentsMounted=!1}onTriggerChange(){this.configureTriggerInteraction()}onIsOpenChange(e,t){!0===e&&!1===t?this.present():!1===e&&!0===t&&this.dismiss()}connectedCallback(){const{configureTriggerInteraction:e,el:t}=this;(0,p.p)(t),e()}disconnectedCallback(){const{destroyTriggerInteraction:e}=this;e&&e()}componentWillLoad(){const{el:e}=this,t=(0,p.s)(e);this.parentPopover=e.closest(`ion-popover:not(#${t})`),void 0===this.alignment&&(this.alignment="ios"===(0,l.g)(this)?"center":"start")}componentDidLoad(){const{parentPopover:e,isOpen:t}=this;!0===t&&(0,n.b)((()=>this.present())),e&&(0,n.a)(e,"ionPopoverWillDismiss",(()=>{this.dismiss(void 0,void 0,!1)}))}async presentFromTrigger(e,t=!1){this.focusDescendantOnPresent=t,await this.present(e),this.focusDescendantOnPresent=!1}getDelegate(e=!1){if(this.workingDelegate&&!e)return{delegate:this.workingDelegate,inline:this.inline};const t=this.el.parentNode,o=this.inline=null!==t&&!this.hasController;return{inline:o,delegate:this.workingDelegate=o?this.delegate||this.coreDelegate:this.delegate}}async present(e){const t=await this.lockController.lock();if(this.presented)return void t();const{el:o}=this,{inline:r,delegate:s}=this.getDelegate(!0);this.ionMount.emit(),this.usersElement=await(0,i.a)(s,o,this.component,["popover-viewport"],this.componentProps,r),this.keyboardEvents||this.configureKeyboardInteraction(),this.configureDismissInteraction(),(0,n.o)(o)?await(0,c.d)(this.usersElement):this.keepContentsMounted||await(0,c.w)(),await(0,p.b)(this,"popoverEnter",T,S,{event:e||this.event,size:this.size,trigger:this.triggerEl,reference:this.reference,side:this.side,align:this.alignment}),this.focusDescendantOnPresent&&(0,p.n)(this.el,this.el),t()}async dismiss(e,t,o=!0){const r=await this.lockController.lock(),{destroyKeyboardInteraction:n,destroyDismissInteraction:s}=this;o&&this.parentPopover&&this.parentPopover.dismiss(e,t,o);const a=await(0,p.d)(this,e,t,"popoverLeave",A,C,this.event);if(a){n&&(n(),this.destroyKeyboardInteraction=void 0),s&&(s(),this.destroyDismissInteraction=void 0);const{delegate:e}=this.getDelegate();await(0,i.d)(e,this.usersElement)}return r(),a}async getParentPopover(){return this.parentPopover}onDidDismiss(){return(0,p.e)(this.el,"ionPopoverDidDismiss")}onWillDismiss(){return(0,p.e)(this.el,"ionPopoverWillDismiss")}render(){const e=(0,l.g)(this),{onLifecycle:t,parentPopover:o,dismissOnSelect:i,side:n,arrow:s,htmlAttributes:a}=this,p=(0,l.a)("desktop"),c=s&&!o;return(0,r.h)(r.H,Object.assign({"aria-modal":"true","no-router":!0,tabindex:"-1"},a,{style:{zIndex:`${2e4+this.overlayIndex}`},class:Object.assign(Object.assign({},(0,d.g)(this.cssClass)),{[e]:!0,"popover-translucent":this.translucent,"overlay-hidden":!0,"popover-desktop":p,[`popover-side-${n}`]:!0,"popover-nested":!!o}),onIonPopoverDidPresent:t,onIonPopoverWillPresent:t,onIonPopoverWillDismiss:t,onIonPopoverDidDismiss:t,onIonBackdropTap:this.onBackdropTap}),!o&&(0,r.h)("ion-backdrop",{tappable:this.backdropDismiss,visible:this.showBackdrop,part:"backdrop"}),(0,r.h)("div",{class:"popover-wrapper ion-overlay-wrapper",onClick:i?()=>this.dismiss():void 0},c&&(0,r.h)("div",{class:"popover-arrow",part:"arrow"}),(0,r.h)("div",{class:"popover-content",part:"content"},(0,r.h)("slot",null))))}get el(){return(0,r.e)(this)}static get watchers(){return{trigger:["onTriggerChange"],triggerAction:["onTriggerChange"],isOpen:["onIsOpenChange"]}}},I={ionPopoverDidPresent:"ionViewDidEnter",ionPopoverWillPresent:"ionViewWillEnter",ionPopoverWillDismiss:"ionViewWillLeave",ionPopoverDidDismiss:"ionViewDidLeave"};L.style={ios:':host{--background:var(--ion-background-color, #fff);--min-width:0;--min-height:0;--max-width:auto;--height:auto;--offset-x:0px;--offset-y:0px;left:0;right:0;top:0;bottom:0;display:flex;position:fixed;align-items:center;justify-content:center;outline:none;color:var(--ion-text-color, #000);z-index:1001}:host(.popover-nested){pointer-events:none}:host(.popover-nested) .popover-wrapper{pointer-events:auto}:host(.overlay-hidden){display:none}.popover-wrapper{z-index:10}.popover-content{display:flex;position:absolute;flex-direction:column;width:var(--width);min-width:var(--min-width);max-width:var(--max-width);height:var(--height);min-height:var(--min-height);max-height:var(--max-height);background:var(--background);box-shadow:var(--box-shadow);overflow:auto;z-index:10}.popover-viewport{--ion-safe-area-top:0px;--ion-safe-area-right:0px;--ion-safe-area-bottom:0px;--ion-safe-area-left:0px;display:flex;flex-direction:column;overflow:hidden}:host(.popover-nested.popover-side-left){--offset-x:5px}:host(.popover-nested.popover-side-right){--offset-x:-5px}:host(.popover-nested.popover-side-start){--offset-x:5px}:host-context([dir=rtl]):host(.popover-nested.popover-side-start),:host-context([dir=rtl]).popover-nested.popover-side-start{--offset-x:-5px}@supports selector(:dir(rtl)){:host(.popover-nested.popover-side-start):dir(rtl){--offset-x:-5px}}:host(.popover-nested.popover-side-end){--offset-x:-5px}:host-context([dir=rtl]):host(.popover-nested.popover-side-end),:host-context([dir=rtl]).popover-nested.popover-side-end{--offset-x:5px}@supports selector(:dir(rtl)){:host(.popover-nested.popover-side-end):dir(rtl){--offset-x:5px}}:host{--width:200px;--max-height:90%;--box-shadow:none;--backdrop-opacity:var(--ion-backdrop-opacity, 0.08)}:host(.popover-desktop){--box-shadow:0px 4px 16px 0px rgba(0, 0, 0, 0.12)}.popover-content{border-radius:10px}:host(.popover-desktop) .popover-content{border:0.5px solid var(--ion-color-step-100, #e6e6e6)}.popover-arrow{display:block;position:absolute;width:20px;height:10px;overflow:hidden}.popover-arrow::after{top:3px;border-radius:3px;position:absolute;width:14px;height:14px;transform:rotate(45deg);background:var(--background);content:"";z-index:10}@supports (inset-inline-start: 0){.popover-arrow::after{inset-inline-start:3px}}@supports not (inset-inline-start: 0){.popover-arrow::after{left:3px}:host-context([dir=rtl]) .popover-arrow::after{left:unset;right:unset;right:3px}[dir=rtl] .popover-arrow::after{left:unset;right:unset;right:3px}@supports selector(:dir(rtl)){.popover-arrow::after:dir(rtl){left:unset;right:unset;right:3px}}}:host(.popover-bottom) .popover-arrow{top:auto;bottom:-10px}:host(.popover-bottom) .popover-arrow::after{top:-6px}:host(.popover-side-left) .popover-arrow{transform:rotate(90deg)}:host(.popover-side-right) .popover-arrow{transform:rotate(-90deg)}:host(.popover-side-top) .popover-arrow{transform:rotate(180deg)}:host(.popover-side-start) .popover-arrow{transform:rotate(90deg)}:host-context([dir=rtl]):host(.popover-side-start) .popover-arrow,:host-context([dir=rtl]).popover-side-start .popover-arrow{transform:rotate(-90deg)}@supports selector(:dir(rtl)){:host(.popover-side-start) .popover-arrow:dir(rtl){transform:rotate(-90deg)}}:host(.popover-side-end) .popover-arrow{transform:rotate(-90deg)}:host-context([dir=rtl]):host(.popover-side-end) .popover-arrow,:host-context([dir=rtl]).popover-side-end .popover-arrow{transform:rotate(90deg)}@supports selector(:dir(rtl)){:host(.popover-side-end) .popover-arrow:dir(rtl){transform:rotate(90deg)}}.popover-arrow,.popover-content{opacity:0}@supports (backdrop-filter: blur(0)){:host(.popover-translucent) .popover-content,:host(.popover-translucent) .popover-arrow::after{background:rgba(var(--ion-background-color-rgb, 255, 255, 255), 0.8);backdrop-filter:saturate(180%) blur(20px)}}',md:":host{--background:var(--ion-background-color, #fff);--min-width:0;--min-height:0;--max-width:auto;--height:auto;--offset-x:0px;--offset-y:0px;left:0;right:0;top:0;bottom:0;display:flex;position:fixed;align-items:center;justify-content:center;outline:none;color:var(--ion-text-color, #000);z-index:1001}:host(.popover-nested){pointer-events:none}:host(.popover-nested) .popover-wrapper{pointer-events:auto}:host(.overlay-hidden){display:none}.popover-wrapper{z-index:10}.popover-content{display:flex;position:absolute;flex-direction:column;width:var(--width);min-width:var(--min-width);max-width:var(--max-width);height:var(--height);min-height:var(--min-height);max-height:var(--max-height);background:var(--background);box-shadow:var(--box-shadow);overflow:auto;z-index:10}.popover-viewport{--ion-safe-area-top:0px;--ion-safe-area-right:0px;--ion-safe-area-bottom:0px;--ion-safe-area-left:0px;display:flex;flex-direction:column;overflow:hidden}:host(.popover-nested.popover-side-left){--offset-x:5px}:host(.popover-nested.popover-side-right){--offset-x:-5px}:host(.popover-nested.popover-side-start){--offset-x:5px}:host-context([dir=rtl]):host(.popover-nested.popover-side-start),:host-context([dir=rtl]).popover-nested.popover-side-start{--offset-x:-5px}@supports selector(:dir(rtl)){:host(.popover-nested.popover-side-start):dir(rtl){--offset-x:-5px}}:host(.popover-nested.popover-side-end){--offset-x:-5px}:host-context([dir=rtl]):host(.popover-nested.popover-side-end),:host-context([dir=rtl]).popover-nested.popover-side-end{--offset-x:5px}@supports selector(:dir(rtl)){:host(.popover-nested.popover-side-end):dir(rtl){--offset-x:5px}}:host{--width:250px;--max-height:90%;--box-shadow:0 5px 5px -3px rgba(0, 0, 0, 0.2), 0 8px 10px 1px rgba(0, 0, 0, 0.14), 0 3px 14px 2px rgba(0, 0, 0, 0.12);--backdrop-opacity:var(--ion-backdrop-opacity, 0.32)}.popover-content{border-radius:4px;transform-origin:left top}:host-context([dir=rtl]) .popover-content{transform-origin:right top}[dir=rtl] .popover-content{transform-origin:right top}@supports selector(:dir(rtl)){.popover-content:dir(rtl){transform-origin:right top}}.popover-viewport{transition-delay:100ms}.popover-wrapper{opacity:0}"}},1706:function(e,t,o){o.d(t,{c:function(){return r}});const r=()=>{let e;return{lock:async()=>{const t=e;let o;return e=new Promise((e=>o=e)),void 0!==t&&await t,o}}}},601:function(e,t,o){o.d(t,{c:function(){return i},g:function(){return n},h:function(){return r},o:function(){return a}});const r=(e,t)=>null!==t.closest(e),i=(e,t)=>"string"==typeof e&&e.length>0?Object.assign({"ion-color":!0,[`ion-color-${e}`]:!0},t):t,n=e=>{const t={};return(e=>void 0!==e?(Array.isArray(e)?e:e.split(" ")).filter((e=>null!=e)).map((e=>e.trim())).filter((e=>""!==e)):[])(e).forEach((e=>t[e]=!0)),t},s=/^[a-z][a-z0-9+\-.]*:/,a=async(e,t,o,r)=>{if(null!=e&&"#"!==e[0]&&!s.test(e)){const i=document.querySelector("ion-router");if(i)return null!=t&&t.preventDefault(),i.push(e,o,r)}return!1}}}]);