/*! For license information please see 6997.bundle.js.LICENSE.txt */
"use strict";(self.webpackChunkamwal_magento_react=self.webpackChunkamwal_magento_react||[]).push([[6997],{6997:function(e,t,n){n.r(t),n.d(t,{KEYBOARD_DID_CLOSE:function(){return o},KEYBOARD_DID_OPEN:function(){return i},copyVisualViewport:function(){return D},keyboardDidClose:function(){return w},keyboardDidOpen:function(){return p},keyboardDidResize:function(){return g},resetKeyboardAssist:function(){return u},setKeyboardClose:function(){return f},setKeyboardOpen:function(){return h},startKeyboardAssist:function(){return d},trackViewportChanges:function(){return y}});const i="ionKeyboardDidShow",o="ionKeyboardDidHide";let r={},a={},s=!1;const u=()=>{r={},a={},s=!1},d=e=>{c(e),e.visualViewport&&(a=D(e.visualViewport),e.visualViewport.onresize=()=>{y(e),p()||g(e)?h(e):w(e)&&f(e)})},c=e=>{e.addEventListener("keyboardDidShow",(t=>h(e,t))),e.addEventListener("keyboardDidHide",(()=>f(e)))},h=(e,t)=>{b(e,t),s=!0},f=e=>{l(e),s=!1},p=()=>{const e=(r.height-a.height)*a.scale;return!s&&r.width===a.width&&e>150},g=e=>s&&!w(e),w=e=>s&&a.height===e.innerHeight,b=(e,t)=>{const n=t?t.keyboardHeight:e.innerHeight-a.height,o=new CustomEvent(i,{detail:{keyboardHeight:n}});e.dispatchEvent(o)},l=e=>{const t=new CustomEvent(o);e.dispatchEvent(t)},y=e=>{r=Object.assign({},a),a=D(e.visualViewport)},D=e=>({width:Math.round(e.width),height:Math.round(e.height),offsetTop:e.offsetTop,offsetLeft:e.offsetLeft,pageTop:e.pageTop,pageLeft:e.pageLeft,scale:e.scale})}}]);