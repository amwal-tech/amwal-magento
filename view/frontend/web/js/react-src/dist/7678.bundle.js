/*! For license information please see 7678.bundle.js.LICENSE.txt */
"use strict";(self.webpackChunkamwal_magento_react=self.webpackChunkamwal_magento_react||[]).push([[7678],{1101:function(t,e,i){i.d(e,{g:function(){return n}});const n=(t,e,i,n,o)=>a(t[1],e[1],i[1],n[1],o).map((a=>s(t[0],e[0],i[0],n[0],a))),s=(t,e,i,n,s)=>s*(3*e*Math.pow(s-1,2)+s*(-3*i*s+3*i+n*s))-t*Math.pow(s-1,3),a=(t,e,i,n,s)=>o((n-=s)-3*(i-=s)+3*(e-=s)-(t-=s),3*i-6*e+3*t,3*e-3*t,t).filter((t=>t>=0&&t<=1)),o=(t,e,i,n)=>{if(0===t)return((t,e,i)=>{const n=e*e-4*t*i;return n<0?[]:[(-e+Math.sqrt(n))/(2*t),(-e-Math.sqrt(n))/(2*t)]})(e,i,n);const s=(3*(i/=t)-(e/=t)*e)/3,a=(2*e*e*e-9*e*i+27*(n/=t))/27;if(0===s)return[Math.pow(-a,1/3)];if(0===a)return[Math.sqrt(-s),-Math.sqrt(-s)];const o=Math.pow(a/2,2)+Math.pow(s/3,3);if(0===o)return[Math.pow(a/2,.5)-e/3];if(o>0)return[Math.pow(-a/2+Math.sqrt(o),1/3)-Math.pow(a/2+Math.sqrt(o),1/3)-e/3];const r=Math.sqrt(Math.pow(-s/3,3)),h=Math.acos(-a/(2*Math.sqrt(Math.pow(-s/3,3)))),d=2*Math.pow(r,1/3);return[d*Math.cos(h/3)-e/3,d*Math.cos((h+2*Math.PI)/3)-e/3,d*Math.cos((h+4*Math.PI)/3)-e/3]}},6465:function(t,e,i){i.d(e,{G:function(){return o}});class n{constructor(t,e,i,n,s){this.id=e,this.name=i,this.disableScroll=s,this.priority=1e6*n+e,this.ctrl=t}canStart(){return!!this.ctrl&&this.ctrl.canStart(this.name)}start(){return!!this.ctrl&&this.ctrl.start(this.name,this.id,this.priority)}capture(){if(!this.ctrl)return!1;const t=this.ctrl.capture(this.name,this.id,this.priority);return t&&this.disableScroll&&this.ctrl.disableScroll(this.id),t}release(){this.ctrl&&(this.ctrl.release(this.id),this.disableScroll&&this.ctrl.enableScroll(this.id))}destroy(){this.release(),this.ctrl=void 0}}class s{constructor(t,e,i,n){this.id=e,this.disable=i,this.disableScroll=n,this.ctrl=t}block(){if(this.ctrl){if(this.disable)for(const t of this.disable)this.ctrl.disableGesture(t,this.id);this.disableScroll&&this.ctrl.disableScroll(this.id)}}unblock(){if(this.ctrl){if(this.disable)for(const t of this.disable)this.ctrl.enableGesture(t,this.id);this.disableScroll&&this.ctrl.enableScroll(this.id)}}destroy(){this.unblock(),this.ctrl=void 0}}const a="backdrop-no-scroll",o=new class{constructor(){this.gestureId=0,this.requestedStart=new Map,this.disabledGestures=new Map,this.disabledScroll=new Set}createGesture(t){var e;return new n(this,this.newID(),t.name,null!==(e=t.priority)&&void 0!==e?e:0,!!t.disableScroll)}createBlocker(t={}){return new s(this,this.newID(),t.disable,!!t.disableScroll)}start(t,e,i){return this.canStart(t)?(this.requestedStart.set(e,i),!0):(this.requestedStart.delete(e),!1)}capture(t,e,i){if(!this.start(t,e,i))return!1;const n=this.requestedStart;let s=-1e4;if(n.forEach((t=>{s=Math.max(s,t)})),s===i){this.capturedId=e,n.clear();const i=new CustomEvent("ionGestureCaptured",{detail:{gestureName:t}});return document.dispatchEvent(i),!0}return n.delete(e),!1}release(t){this.requestedStart.delete(t),this.capturedId===t&&(this.capturedId=void 0)}disableGesture(t,e){let i=this.disabledGestures.get(t);void 0===i&&(i=new Set,this.disabledGestures.set(t,i)),i.add(e)}enableGesture(t,e){const i=this.disabledGestures.get(t);void 0!==i&&i.delete(e)}disableScroll(t){this.disabledScroll.add(t),1===this.disabledScroll.size&&document.body.classList.add(a)}enableScroll(t){this.disabledScroll.delete(t),0===this.disabledScroll.size&&document.body.classList.remove(a)}canStart(t){return void 0===this.capturedId&&!this.isDisabled(t)}isCaptured(){return void 0!==this.capturedId}isScrollDisabled(){return this.disabledScroll.size>0}isDisabled(t){const e=this.disabledGestures.get(t);return!!(e&&e.size>0)}newID(){return this.gestureId++,this.gestureId}}},5904:function(t,e,i){i.d(e,{m:function(){return c}});var n=i(3617),s=i(8248),a=i(399),o=i(8721);const r=t=>(0,o.c)().duration(t?400:300),h=t=>{let e,i;const n=t.width+8,s=(0,o.c)(),h=(0,o.c)();t.isEndSide?(e=n+"px",i="0px"):(e=-n+"px",i="0px"),s.addElement(t.menuInnerEl).fromTo("transform",`translateX(${e})`,`translateX(${i})`);const d="ios"===(0,a.g)(t),l=d?.2:.25;return h.addElement(t.backdropEl).fromTo("opacity",.01,l),r(d).addAnimation([s,h])},d=t=>{let e,i;const n=(0,a.g)(t),s=t.width;t.isEndSide?(e=-s+"px",i=s+"px"):(e=s+"px",i=-s+"px");const h=(0,o.c)().addElement(t.menuInnerEl).fromTo("transform",`translateX(${i})`,"translateX(0px)"),d=(0,o.c)().addElement(t.contentEl).fromTo("transform","translateX(0px)",`translateX(${e})`),l=(0,o.c)().addElement(t.backdropEl).fromTo("opacity",.01,.32);return r("ios"===n).addAnimation([h,d,l])},l=t=>{const e=(0,a.g)(t),i=t.width*(t.isEndSide?-1:1)+"px",n=(0,o.c)().addElement(t.contentEl).fromTo("transform","translateX(0px)",`translateX(${i})`);return r("ios"===e).addAnimation(n)},c=(()=>{const t=new Map,e=[],i=async t=>{if(await p(),"start"===t||"end"===t){return m((e=>e.side===t&&!e.disabled))||m((e=>e.side===t))}if(null!=t)return m((e=>e.menuId===t));return m((t=>!t.disabled))||(e.length>0?e[0].el:void 0)},a=async()=>(await p(),c()),o=(e,i)=>{t.set(e,i)},r=t=>{const i=t.side;e.filter((e=>e.side===i&&e!==t)).forEach((t=>t.disabled=!0))},c=()=>m((t=>t._isOpen)),u=()=>e.some((t=>t.isAnimating)),m=t=>{const i=e.find(t);if(void 0!==i)return i.el},p=()=>Promise.all(Array.from(document.querySelectorAll("ion-menu")).map((t=>new Promise((e=>(0,s.c)(t,e))))));return o("reveal",l),o("push",d),o("overlay",h),"undefined"!=typeof document&&document.addEventListener("ionBackButton",(t=>{const e=c();e&&t.detail.register(n.MENU_BACK_BUTTON_PRIORITY,(()=>e.close()))})),{registerAnimation:o,get:i,getMenus:async()=>(await p(),e.map((t=>t.el))),getOpen:a,isEnabled:async t=>{const e=await i(t);return!!e&&!e.disabled},swipeGesture:async(t,e)=>{const n=await i(e);return n&&(n.swipeGesture=t),n},isAnimating:async()=>(await p(),u()),isOpen:async t=>{if(null!=t){const e=await i(t);return void 0!==e&&e.isOpen()}return void 0!==await a()},enable:async(t,e)=>{const n=await i(e);return n&&(n.disabled=!t),n},toggle:async t=>{const e=await i(t);return!!e&&e.toggle()},close:async t=>{const e=await(void 0!==t?i(t):a());return void 0!==e&&e.close()},open:async t=>{const e=await i(t);return!!e&&e.open()},_getOpenSync:c,_createAnimation:(e,i)=>{const n=t.get(e);if(!n)throw new Error("animation not registered");return n(i)},_register:t=>{e.indexOf(t)<0&&(t.disabled||r(t),e.push(t))},_unregister:t=>{const i=e.indexOf(t);i>-1&&e.splice(i,1)},_setOpen:async(t,e,i)=>{if(u())return!1;if(e){const e=await a();e&&t.el!==e&&await e.setOpen(!1,!1)}return t._setOpen(e,i)},_setActiveMenu:r}})()},2098:function(t,e,i){i.r(e),i.d(e,{ion_menu:function(){return c}});var n=i(2170),s=i(399),a=i(1101),o=i(6465),r=i(8248),h=i(5904),d=i(4022);i(8721),i(4314);const l='[tabindex]:not([tabindex^="-"]), input:not([type=hidden]):not([tabindex^="-"]), textarea:not([tabindex^="-"]), button:not([tabindex^="-"]), select:not([tabindex^="-"]), .ion-focusable:not([tabindex^="-"])',c=class{constructor(t){(0,n.r)(this,t),this.ionWillOpen=(0,n.d)(this,"ionWillOpen",7),this.ionWillClose=(0,n.d)(this,"ionWillClose",7),this.ionDidOpen=(0,n.d)(this,"ionDidOpen",7),this.ionDidClose=(0,n.d)(this,"ionDidClose",7),this.ionMenuChange=(0,n.d)(this,"ionMenuChange",7),this.lastOnEnd=0,this.blocker=o.G.createBlocker({disableScroll:!0}),this.isAnimating=!1,this._isOpen=!1,this.inheritedAttributes={},this.handleFocus=t=>{const e=(0,d.g)(document);e&&!e.contains(this.el)||this.trapKeyboardFocus(t,document)},this.isPaneVisible=!1,this.isEndSide=!1,this.contentId=void 0,this.menuId=void 0,this.type=void 0,this.disabled=!1,this.side="start",this.swipeGesture=!0,this.maxEdgeStart=50}typeChanged(t,e){const i=this.contentEl;i&&(void 0!==e&&i.classList.remove(`menu-content-${e}`),i.classList.add(`menu-content-${t}`),i.removeAttribute("style")),this.menuInnerEl&&this.menuInnerEl.removeAttribute("style"),this.animation=void 0}disabledChanged(){this.updateState(),this.ionMenuChange.emit({disabled:this.disabled,open:this._isOpen})}sideChanged(){this.isEndSide=(0,r.m)(this.side),this.animation=void 0}swipeGestureChanged(){this.updateState()}async connectedCallback(){"undefined"!=typeof customElements&&null!=customElements&&await customElements.whenDefined("ion-menu"),void 0===this.type&&(this.type=s.c.get("menuType","overlay"));const t=void 0!==this.contentId?document.getElementById(this.contentId):null;null!==t?(this.el.contains(t)&&console.error('Menu: "contentId" should refer to the main view\'s ion-content, not the ion-content inside of the ion-menu.'),this.contentEl=t,t.classList.add("menu-content"),this.typeChanged(this.type,void 0),this.sideChanged(),h.m._register(this),this.gesture=(await i.e(4442).then(i.bind(i,4442))).createGesture({el:document,gestureName:"menu-swipe",gesturePriority:30,threshold:10,blurOnStart:!0,canStart:t=>this.canStart(t),onWillStart:()=>this.onWillStart(),onStart:()=>this.onStart(),onMove:t=>this.onMove(t),onEnd:t=>this.onEnd(t)}),this.updateState()):console.error('Menu: must have a "content" element to listen for drag events on.')}componentWillLoad(){this.inheritedAttributes=(0,r.i)(this.el)}async componentDidLoad(){this.ionMenuChange.emit({disabled:this.disabled,open:this._isOpen}),this.updateState()}async disconnectedCallback(){await this.close(!1),this.blocker.destroy(),h.m._unregister(this),this.animation&&this.animation.destroy(),this.gesture&&(this.gesture.destroy(),this.gesture=void 0),this.animation=void 0,this.contentEl=void 0}onSplitPaneChanged(t){this.isPaneVisible=t.detail.isPane(this.el),this.updateState()}onBackdropClick(t){this._isOpen&&this.lastOnEnd<t.timeStamp-100&&t.composedPath&&!t.composedPath().includes(this.menuInnerEl)&&(t.preventDefault(),t.stopPropagation(),this.close())}onKeydown(t){"Escape"===t.key&&this.close()}isOpen(){return Promise.resolve(this._isOpen)}isActive(){return Promise.resolve(this._isActive())}open(t=!0){return this.setOpen(!0,t)}close(t=!0){return this.setOpen(!1,t)}toggle(t=!0){return this.setOpen(!this._isOpen,t)}setOpen(t,e=!0){return h.m._setOpen(this,t,e)}focusFirstDescendant(){const{el:t}=this,e=t.querySelector(l);e?e.focus():t.focus()}focusLastDescendant(){const{el:t}=this,e=Array.from(t.querySelectorAll(l)),i=e.length>0?e[e.length-1]:null;i?i.focus():t.focus()}trapKeyboardFocus(t,e){const i=t.target;i&&(this.el.contains(i)?this.lastFocus=i:(this.focusFirstDescendant(),this.lastFocus===e.activeElement&&this.focusLastDescendant()))}async _setOpen(t,e=!0){return!(!this._isActive()||this.isAnimating||t===this._isOpen||(this.beforeAnimation(t),await this.loadAnimation(),await this.startAnimation(t,e),this.afterAnimation(t),0))}async loadAnimation(){const t=this.menuInnerEl.offsetWidth,e=(0,r.m)(this.side);t===this.width&&void 0!==this.animation&&e===this.isEndSide||(this.width=t,this.isEndSide=e,this.animation&&(this.animation.destroy(),this.animation=void 0),this.animation=await h.m._createAnimation(this.type,this),s.c.getBoolean("animated",!0)||this.animation.duration(0),this.animation.fill("both"))}async startAnimation(t,e){const i=!t,n=(0,s.g)(this),a="ios"===n?"cubic-bezier(0.32,0.72,0,1)":"cubic-bezier(0.0,0.0,0.2,1)",o="ios"===n?"cubic-bezier(1, 0, 0.68, 0.28)":"cubic-bezier(0.4, 0, 0.6, 1)",r=this.animation.direction(i?"reverse":"normal").easing(i?o:a).onFinish((()=>{"reverse"===r.getDirection()&&r.direction("normal")}));e?await r.play():r.play({sync:!0})}_isActive(){return!this.disabled&&!this.isPaneVisible}canSwipe(){return this.swipeGesture&&!this.isAnimating&&this._isActive()}canStart(t){return!(document.querySelector("ion-modal.show-modal")||!this.canSwipe())&&(!!this._isOpen||!h.m._getOpenSync()&&m(window,t.currentX,this.isEndSide,this.maxEdgeStart))}onWillStart(){return this.beforeAnimation(!this._isOpen),this.loadAnimation()}onStart(){this.isAnimating&&this.animation?this.animation.progressStart(!0,this._isOpen?1:0):(0,r.n)(!1,"isAnimating has to be true")}onMove(t){if(!this.isAnimating||!this.animation)return void(0,r.n)(!1,"isAnimating has to be true");const e=u(t.deltaX,this._isOpen,this.isEndSide)/this.width;this.animation.progressStep(this._isOpen?1-e:e)}onEnd(t){if(!this.isAnimating||!this.animation)return void(0,r.n)(!1,"isAnimating has to be true");const e=this._isOpen,i=this.isEndSide,n=u(t.deltaX,e,i),s=this.width,o=n/s,h=t.velocityX,d=s/2,l=h>=0&&(h>.2||t.deltaX>d),c=h<=0&&(h<-.2||t.deltaX<-d),m=e?i?l:c:i?c:l;let p=!e&&m;e&&!m&&(p=!0),this.lastOnEnd=t.currentTime;let b=m?.001:-.001;const g=o<0?.01:o;b+=(0,a.g)([0,0],[.4,0],[.6,1],[1,1],(0,r.j)(0,g,.9999))[0]||0;const f=this._isOpen?!m:m;this.animation.easing("cubic-bezier(0.4, 0.0, 0.6, 1)").onFinish((()=>this.afterAnimation(p)),{oneTimeCallback:!0}).progressEnd(f?1:0,this._isOpen?1-b:b,300)}beforeAnimation(t){(0,r.n)(!this.isAnimating,"_before() should not be called while animating"),this.el.classList.add(p),this.el.setAttribute("tabindex","0"),this.backdropEl&&this.backdropEl.classList.add(b),this.contentEl&&(this.contentEl.classList.add(g),this.contentEl.setAttribute("aria-hidden","true")),this.blocker.block(),this.isAnimating=!0,t?this.ionWillOpen.emit():this.ionWillClose.emit()}afterAnimation(t){var e;(0,r.n)(this.isAnimating,"_before() should be called while animating"),this._isOpen=t,this.isAnimating=!1,this._isOpen||this.blocker.unblock(),t?(this.ionDidOpen.emit(),(null===(e=document.activeElement)||void 0===e?void 0:e.closest("ion-menu"))!==this.el&&this.el.focus(),document.addEventListener("focus",this.handleFocus,!0)):(this.el.classList.remove(p),this.el.removeAttribute("tabindex"),this.contentEl&&(this.contentEl.classList.remove(g),this.contentEl.removeAttribute("aria-hidden")),this.backdropEl&&this.backdropEl.classList.remove(b),this.animation&&this.animation.stop(),this.ionDidClose.emit(),document.removeEventListener("focus",this.handleFocus,!0))}updateState(){const t=this._isActive();this.gesture&&this.gesture.enable(t&&this.swipeGesture),!t&&this._isOpen&&this.forceClosing(),this.disabled||h.m._setActiveMenu(this),(0,r.n)(!this.isAnimating,"can not be animating")}forceClosing(){(0,r.n)(this._isOpen,"menu cannot be closed"),this.isAnimating=!0,this.animation.direction("reverse").play({sync:!0}),this.afterAnimation(!1)}render(){const{type:t,disabled:e,isPaneVisible:i,inheritedAttributes:a,side:o}=this,r=(0,s.g)(this);return(0,n.h)(n.H,{role:"navigation","aria-label":a["aria-label"]||"menu",class:{[r]:!0,[`menu-type-${t}`]:!0,"menu-enabled":!e,[`menu-side-${o}`]:!0,"menu-pane-visible":i}},(0,n.h)("div",{class:"menu-inner",part:"container",ref:t=>this.menuInnerEl=t},(0,n.h)("slot",null)),(0,n.h)("ion-backdrop",{ref:t=>this.backdropEl=t,class:"menu-backdrop",tappable:!1,stopPropagation:!1,part:"backdrop"}))}get el(){return(0,n.e)(this)}static get watchers(){return{type:["typeChanged"],disabled:["disabledChanged"],side:["sideChanged"],swipeGesture:["swipeGestureChanged"]}}},u=(t,e,i)=>Math.max(0,e!==i?-t:t),m=(t,e,i,n)=>i?e>=t.innerWidth-n:e<=n,p="show-menu",b="show-backdrop",g="menu-content-open";c.style={ios:":host{--width:304px;--min-width:auto;--max-width:auto;--height:100%;--min-height:auto;--max-height:auto;--background:var(--ion-background-color, #fff);left:0;right:0;top:0;bottom:0;display:none;position:absolute;contain:strict}:host(.show-menu){display:block}.menu-inner{transform:translateX(-9999px);display:flex;position:absolute;flex-direction:column;justify-content:space-between;width:var(--width);min-width:var(--min-width);max-width:var(--max-width);height:var(--height);min-height:var(--min-height);max-height:var(--max-height);background:var(--background);contain:strict}:host(.menu-side-start) .menu-inner{--ion-safe-area-right:0px;top:0;bottom:0}@supports (inset-inline-start: 0){:host(.menu-side-start) .menu-inner{inset-inline-start:0;inset-inline-end:auto}}@supports not (inset-inline-start: 0){:host(.menu-side-start) .menu-inner{left:0;right:auto}:host-context([dir=rtl]):host(.menu-side-start) .menu-inner,:host-context([dir=rtl]).menu-side-start .menu-inner{left:unset;right:unset;left:auto;right:0}}:host(.menu-side-end) .menu-inner{--ion-safe-area-left:0px;top:0;bottom:0}@supports (inset-inline-start: 0){:host(.menu-side-end) .menu-inner{inset-inline-start:auto;inset-inline-end:0}}@supports not (inset-inline-start: 0){:host(.menu-side-end) .menu-inner{left:auto;right:0}:host-context([dir=rtl]):host(.menu-side-end) .menu-inner,:host-context([dir=rtl]).menu-side-end .menu-inner{left:unset;right:unset;left:0;right:auto}}ion-backdrop{display:none;opacity:0.01;z-index:-1}@media (max-width: 340px){.menu-inner{--width:264px}}:host(.menu-type-reveal){z-index:0}:host(.menu-type-reveal.show-menu) .menu-inner{transform:translate3d(0,  0,  0)}:host(.menu-type-overlay){z-index:1000}:host(.menu-type-overlay) .show-backdrop{display:block;cursor:pointer}:host(.menu-pane-visible){width:var(--width);min-width:var(--min-width);max-width:var(--max-width)}:host(.menu-pane-visible) .menu-inner{left:0;right:0;width:auto;transform:none !important;box-shadow:none !important}:host(.menu-pane-visible) ion-backdrop{display:hidden !important;}:host(.menu-type-push){z-index:1000}:host(.menu-type-push) .show-backdrop{display:block}",md:":host{--width:304px;--min-width:auto;--max-width:auto;--height:100%;--min-height:auto;--max-height:auto;--background:var(--ion-background-color, #fff);left:0;right:0;top:0;bottom:0;display:none;position:absolute;contain:strict}:host(.show-menu){display:block}.menu-inner{transform:translateX(-9999px);display:flex;position:absolute;flex-direction:column;justify-content:space-between;width:var(--width);min-width:var(--min-width);max-width:var(--max-width);height:var(--height);min-height:var(--min-height);max-height:var(--max-height);background:var(--background);contain:strict}:host(.menu-side-start) .menu-inner{--ion-safe-area-right:0px;top:0;bottom:0}@supports (inset-inline-start: 0){:host(.menu-side-start) .menu-inner{inset-inline-start:0;inset-inline-end:auto}}@supports not (inset-inline-start: 0){:host(.menu-side-start) .menu-inner{left:0;right:auto}:host-context([dir=rtl]):host(.menu-side-start) .menu-inner,:host-context([dir=rtl]).menu-side-start .menu-inner{left:unset;right:unset;left:auto;right:0}}:host(.menu-side-end) .menu-inner{--ion-safe-area-left:0px;top:0;bottom:0}@supports (inset-inline-start: 0){:host(.menu-side-end) .menu-inner{inset-inline-start:auto;inset-inline-end:0}}@supports not (inset-inline-start: 0){:host(.menu-side-end) .menu-inner{left:auto;right:0}:host-context([dir=rtl]):host(.menu-side-end) .menu-inner,:host-context([dir=rtl]).menu-side-end .menu-inner{left:unset;right:unset;left:0;right:auto}}ion-backdrop{display:none;opacity:0.01;z-index:-1}@media (max-width: 340px){.menu-inner{--width:264px}}:host(.menu-type-reveal){z-index:0}:host(.menu-type-reveal.show-menu) .menu-inner{transform:translate3d(0,  0,  0)}:host(.menu-type-overlay){z-index:1000}:host(.menu-type-overlay) .show-backdrop{display:block;cursor:pointer}:host(.menu-pane-visible){width:var(--width);min-width:var(--min-width);max-width:var(--max-width)}:host(.menu-pane-visible) .menu-inner{left:0;right:0;width:auto;transform:none !important;box-shadow:none !important}:host(.menu-pane-visible) ion-backdrop{display:hidden !important;}:host(.menu-type-overlay) .menu-inner{box-shadow:4px 0px 16px rgba(0, 0, 0, 0.18)}"}}}]);