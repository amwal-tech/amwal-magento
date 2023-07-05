/*! For license information please see 1461.bundle.js.LICENSE.txt */
"use strict";(self.webpackChunkamwal_magento_react=self.webpackChunkamwal_magento_react||[]).push([[1461,4101,4442],{7460:function(t,e,r){r.d(e,{i:function(){return s}});const s=t=>t&&""!==t.dir?"rtl"===t.dir.toLowerCase():"rtl"===(null===document||void 0===document?void 0:document.dir.toLowerCase())},6465:function(t,e,r){r.d(e,{G:function(){return a}});class s{constructor(t,e,r,s,i){this.id=e,this.name=r,this.disableScroll=i,this.priority=1e6*s+e,this.ctrl=t}canStart(){return!!this.ctrl&&this.ctrl.canStart(this.name)}start(){return!!this.ctrl&&this.ctrl.start(this.name,this.id,this.priority)}capture(){if(!this.ctrl)return!1;const t=this.ctrl.capture(this.name,this.id,this.priority);return t&&this.disableScroll&&this.ctrl.disableScroll(this.id),t}release(){this.ctrl&&(this.ctrl.release(this.id),this.disableScroll&&this.ctrl.enableScroll(this.id))}destroy(){this.release(),this.ctrl=void 0}}class i{constructor(t,e,r,s){this.id=e,this.disable=r,this.disableScroll=s,this.ctrl=t}block(){if(this.ctrl){if(this.disable)for(const t of this.disable)this.ctrl.disableGesture(t,this.id);this.disableScroll&&this.ctrl.disableScroll(this.id)}}unblock(){if(this.ctrl){if(this.disable)for(const t of this.disable)this.ctrl.enableGesture(t,this.id);this.disableScroll&&this.ctrl.enableScroll(this.id)}}destroy(){this.unblock(),this.ctrl=void 0}}const n="backdrop-no-scroll",a=new class{constructor(){this.gestureId=0,this.requestedStart=new Map,this.disabledGestures=new Map,this.disabledScroll=new Set}createGesture(t){var e;return new s(this,this.newID(),t.name,null!==(e=t.priority)&&void 0!==e?e:0,!!t.disableScroll)}createBlocker(t={}){return new i(this,this.newID(),t.disable,!!t.disableScroll)}start(t,e,r){return this.canStart(t)?(this.requestedStart.set(e,r),!0):(this.requestedStart.delete(e),!1)}capture(t,e,r){if(!this.start(t,e,r))return!1;const s=this.requestedStart;let i=-1e4;if(s.forEach((t=>{i=Math.max(i,t)})),i===r){this.capturedId=e,s.clear();const r=new CustomEvent("ionGestureCaptured",{detail:{gestureName:t}});return document.dispatchEvent(r),!0}return s.delete(e),!1}release(t){this.requestedStart.delete(t),this.capturedId===t&&(this.capturedId=void 0)}disableGesture(t,e){let r=this.disabledGestures.get(t);void 0===r&&(r=new Set,this.disabledGestures.set(t,r)),r.add(e)}enableGesture(t,e){const r=this.disabledGestures.get(t);void 0!==r&&r.delete(e)}disableScroll(t){this.disabledScroll.add(t),1===this.disabledScroll.size&&document.body.classList.add(n)}enableScroll(t){this.disabledScroll.delete(t),0===this.disabledScroll.size&&document.body.classList.remove(n)}canStart(t){return void 0===this.capturedId&&!this.isDisabled(t)}isCaptured(){return void 0!==this.capturedId}isScrollDisabled(){return this.disabledScroll.size>0}isDisabled(t){const e=this.disabledGestures.get(t);return!!(e&&e.size>0)}newID(){return this.gestureId++,this.gestureId}}},4442:function(t,e,r){r.r(e),r.d(e,{GESTURE_CONTROLLER:function(){return s.G},createGesture:function(){return o}});var s=r(6465);const i=(t,e,r,s)=>{const i=n(t)?{capture:!!s.capture,passive:!!s.passive}:!!s.capture;let a,c;return t.__zone_symbol__addEventListener?(a="__zone_symbol__addEventListener",c="__zone_symbol__removeEventListener"):(a="addEventListener",c="removeEventListener"),t[a](e,r,i),()=>{t[c](e,r,i)}},n=t=>{if(void 0===a)try{const e=Object.defineProperty({},"passive",{get:()=>{a=!0}});t.addEventListener("optsTest",(()=>{}),e)}catch(t){a=!1}return!!a};let a;const c=t=>t instanceof Document?t:t.ownerDocument,o=t=>{let e=!1,r=!1,n=!0,a=!1;const o=Object.assign({disableScroll:!1,direction:"x",gesturePriority:0,passive:!0,maxAngle:40,threshold:10},t),h=o.canStart,b=o.onWillStart,v=o.onStart,m=o.onEnd,p=o.notCaptured,S=o.onMove,f=o.threshold,y=o.passive,g=o.blurOnStart,X={type:"pan",startX:0,startY:0,startTime:0,currentX:0,currentY:0,velocityX:0,velocityY:0,deltaX:0,deltaY:0,currentTime:0,event:void 0,data:void 0},w=((t,e,r)=>{const s=r*(Math.PI/180),i="x"===t,n=Math.cos(s),a=e*e;let c=0,o=0,l=!1,d=0;return{start(t,e){c=t,o=e,d=0,l=!0},detect(t,e){if(!l)return!1;const r=t-c,s=e-o,u=r*r+s*s;if(u<a)return!1;const h=Math.sqrt(u),b=(i?r:s)/h;return d=b>n?1:b<-n?-1:0,l=!1,!0},isGesture(){return 0!==d},getDirection(){return d}}})(o.direction,o.threshold,o.maxAngle),G=s.G.createGesture({name:t.gestureName,priority:t.gesturePriority,disableScroll:t.disableScroll}),Y=()=>{e&&(a=!1,S&&S(X))},_=()=>!!G.capture()&&(e=!0,n=!1,X.startX=X.currentX,X.startY=X.currentY,X.startTime=X.currentTime,b?b(X).then(E):E(),!0),E=()=>{g&&(()=>{if("undefined"!=typeof document){const t=document.activeElement;(null==t?void 0:t.blur)&&t.blur()}})(),v&&v(X),n=!0},D=()=>{e=!1,r=!1,a=!1,n=!0,G.release()},I=t=>{const r=e,s=n;D(),s&&(l(X,t),r?m&&m(X):p&&p(X))},L=((t,e,r,s,n)=>{let a,o,l,d,u,h,b,v=0;const m=s=>{v=Date.now()+2e3,e(s)&&(!o&&r&&(o=i(t,"touchmove",r,n)),l||(l=i(s.target,"touchend",S,n)),d||(d=i(s.target,"touchcancel",S,n)))},p=s=>{v>Date.now()||e(s)&&(!h&&r&&(h=i(c(t),"mousemove",r,n)),b||(b=i(c(t),"mouseup",f,n)))},S=t=>{y(),s&&s(t)},f=t=>{g(),s&&s(t)},y=()=>{o&&o(),l&&l(),d&&d(),o=l=d=void 0},g=()=>{h&&h(),b&&b(),h=b=void 0},X=()=>{y(),g()},w=(e=!0)=>{e?(a||(a=i(t,"touchstart",m,n)),u||(u=i(t,"mousedown",p,n))):(a&&a(),u&&u(),a=u=void 0,X())};return{enable:w,stop:X,destroy:()=>{w(!1),s=r=e=void 0}}})(o.el,(t=>{const e=u(t);return!(r||!n)&&(d(t,X),X.startX=X.currentX,X.startY=X.currentY,X.startTime=X.currentTime=e,X.velocityX=X.velocityY=X.deltaX=X.deltaY=0,X.event=t,(!h||!1!==h(X))&&(G.release(),!!G.start()&&(r=!0,0===f?_():(w.start(X.startX,X.startY),!0))))}),(t=>{e?!a&&n&&(a=!0,l(X,t),requestAnimationFrame(Y)):(l(X,t),w.detect(X.currentX,X.currentY)&&(w.isGesture()&&_()||T()))}),I,{capture:!1,passive:y}),T=()=>{D(),L.stop(),p&&p(X)};return{enable(t=!0){t||(e&&I(void 0),D()),L.enable(t)},destroy(){G.destroy(),L.destroy()}}},l=(t,e)=>{if(!e)return;const r=t.currentX,s=t.currentY,i=t.currentTime;d(e,t);const n=t.currentX,a=t.currentY,c=(t.currentTime=u(e))-i;if(c>0&&c<100){const e=(n-r)/c,i=(a-s)/c;t.velocityX=.7*e+.3*t.velocityX,t.velocityY=.7*i+.3*t.velocityY}t.deltaX=n-t.startX,t.deltaY=a-t.startY,t.event=e},d=(t,e)=>{let r=0,s=0;if(t){const e=t.changedTouches;if(e&&e.length>0){const t=e[0];r=t.clientX,s=t.clientY}else void 0!==t.pageX&&(r=t.pageX,s=t.pageY)}e.currentX=r,e.currentY=s},u=t=>t.timeStamp||Date.now()},1461:function(t,e,r){r.r(e),r.d(e,{createSwipeBackGesture:function(){return a}});var s=r(8248),i=r(7460),n=r(4442);r(6465);const a=(t,e,r,a,c)=>{const o=t.ownerDocument.defaultView;let l=(0,i.i)(t);const d=t=>l?-t.deltaX:t.deltaX;return(0,n.createGesture)({el:t,gestureName:"goback-swipe",gesturePriority:40,threshold:10,canStart:r=>(l=(0,i.i)(t),(t=>{const{startX:e}=t;return l?e>=o.innerWidth-50:e<=50})(r)&&e()),onStart:r,onMove:t=>{const e=d(t)/o.innerWidth;a(e)},onEnd:t=>{const e=d(t),r=o.innerWidth,i=e/r,n=(t=>l?-t.velocityX:t.velocityX)(t),a=n>=0&&(n>.2||e>r/2),u=(a?1-i:i)*r;let h=0;if(u>5){const t=u/Math.abs(n);h=Math.min(t,540)}c(a,i<=0?.01:(0,s.j)(0,i,.9999),h)}})}}}]);