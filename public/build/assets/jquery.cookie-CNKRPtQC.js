var w=(n,u)=>()=>(u||n((u={exports:{}}).exports,u),u.exports);var S=w(x=>{/*!
 * jQuery Cookie Plugin v1.4.1
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2013 Klaus Hartl
 * Released under the MIT license
 */(function(n){typeof define=="function"&&define.amd?define(["jquery"],n):n(typeof x=="object"?require("jquery"):$)})(function(n){var u=/\+/g;function o(e){return t.raw?e:encodeURIComponent(e)}function g(e){return t.raw?e:decodeURIComponent(e)}function v(e){return o(t.json?JSON.stringify(e):String(e))}function C(e){e.indexOf('"')===0&&(e=e.slice(1,-1).replace(/\\"/g,'"').replace(/\\\\/g,"\\"));try{return e=decodeURIComponent(e.replace(u," ")),t.json?JSON.parse(e):e}catch{}}function a(e,i){var r=t.raw?e:C(e);return n.isFunction(i)?i(r):r}var t=n.cookie=function(e,i,r){if(i!==void 0&&!n.isFunction(i)){if(r=n.extend({},t.defaults,r),typeof r.expires=="number"){var h=r.expires,l=r.expires=new Date;l.setTime(+l+h*864e5)}return document.cookie=[o(e),"=",v(i),r.expires?"; expires="+r.expires.toUTCString():"",r.path?"; path="+r.path:"",r.domain?"; domain="+r.domain:"",r.secure?"; secure":""].join("")}for(var c=e?void 0:{},m=document.cookie?document.cookie.split("; "):[],d=0,j=m.length;d<j;d++){var p=m[d].split("="),s=g(p.shift()),f=p.join("=");if(e&&e===s){c=a(f,i);break}!e&&(f=a(f))!==void 0&&(c[s]=f)}return c};t.defaults={},n.removeCookie=function(e,i){return n.cookie(e)===void 0?!1:(n.cookie(e,"",n.extend({},i,{expires:-1})),!n.cookie(e))}})});export default S();
