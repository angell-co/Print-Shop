(function(t){function e(e){for(var r,o,c=e[0],f=e[1],i=e[2],l=0,u=[];l<c.length;l++)o=c[l],n[o]&&u.push(n[o][0]),n[o]=0;for(r in f)Object.prototype.hasOwnProperty.call(f,r)&&(t[r]=f[r]);d&&d(e);while(u.length)u.shift()();return a.push.apply(a,i||[]),s()}function s(){for(var t,e=0;e<a.length;e++){for(var s=a[e],r=!0,c=1;c<s.length;c++){var f=s[c];0!==n[f]&&(r=!1)}r&&(a.splice(e--,1),t=o(o.s=s[0]))}return t}var r={},n={app:0},a=[];function o(e){if(r[e])return r[e].exports;var s=r[e]={i:e,l:!1,exports:{}};return t[e].call(s.exports,s,s.exports,o),s.l=!0,s.exports}o.m=t,o.c=r,o.d=function(t,e,s){o.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:s})},o.r=function(t){"undefined"!==typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},o.t=function(t,e){if(1&e&&(t=o(t)),8&e)return t;if(4&e&&"object"===typeof t&&t&&t.__esModule)return t;var s=Object.create(null);if(o.r(s),Object.defineProperty(s,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var r in t)o.d(s,r,function(e){return t[e]}.bind(null,r));return s},o.n=function(t){var e=t&&t.__esModule?function(){return t["default"]}:function(){return t};return o.d(e,"a",e),e},o.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},o.p="/";var c=window["webpackJsonp"]=window["webpackJsonp"]||[],f=c.push.bind(c);c.push=e,c=c.slice();for(var i=0;i<c.length;i++)e(c[i]);var d=f;a.push([0,"chunk-vendors"]),s()})({0:function(t,e,s){t.exports=s("56d7")},"239c":function(t,e,s){},4678:function(t,e,s){var r={"./af":"2bfb","./af.js":"2bfb","./ar":"8e73","./ar-dz":"a356","./ar-dz.js":"a356","./ar-kw":"423e","./ar-kw.js":"423e","./ar-ly":"1cfd","./ar-ly.js":"1cfd","./ar-ma":"0a84","./ar-ma.js":"0a84","./ar-sa":"8230","./ar-sa.js":"8230","./ar-tn":"6d83","./ar-tn.js":"6d83","./ar.js":"8e73","./az":"485c","./az.js":"485c","./be":"1fc1","./be.js":"1fc1","./bg":"84aa","./bg.js":"84aa","./bm":"a7fa","./bm.js":"a7fa","./bn":"9043","./bn.js":"9043","./bo":"d26a","./bo.js":"d26a","./br":"6887","./br.js":"6887","./bs":"2554","./bs.js":"2554","./ca":"d716","./ca.js":"d716","./cs":"3c0d","./cs.js":"3c0d","./cv":"03ec","./cv.js":"03ec","./cy":"9797","./cy.js":"9797","./da":"0f14","./da.js":"0f14","./de":"b469","./de-at":"b3eb","./de-at.js":"b3eb","./de-ch":"bb71","./de-ch.js":"bb71","./de.js":"b469","./dv":"598a","./dv.js":"598a","./el":"8d47","./el.js":"8d47","./en-SG":"cdab","./en-SG.js":"cdab","./en-au":"0e6b","./en-au.js":"0e6b","./en-ca":"3886","./en-ca.js":"3886","./en-gb":"39a6","./en-gb.js":"39a6","./en-ie":"e1d3","./en-ie.js":"e1d3","./en-il":"7333","./en-il.js":"7333","./en-nz":"6f50","./en-nz.js":"6f50","./eo":"65db","./eo.js":"65db","./es":"898b","./es-do":"0a3c","./es-do.js":"0a3c","./es-us":"55c9","./es-us.js":"55c9","./es.js":"898b","./et":"ec18","./et.js":"ec18","./eu":"0ff2","./eu.js":"0ff2","./fa":"8df4","./fa.js":"8df4","./fi":"81e9","./fi.js":"81e9","./fo":"0721","./fo.js":"0721","./fr":"9f26","./fr-ca":"d9f8","./fr-ca.js":"d9f8","./fr-ch":"0e49","./fr-ch.js":"0e49","./fr.js":"9f26","./fy":"7118","./fy.js":"7118","./ga":"5120","./ga.js":"5120","./gd":"f6b4","./gd.js":"f6b4","./gl":"8840","./gl.js":"8840","./gom-latn":"0caa","./gom-latn.js":"0caa","./gu":"e0c5","./gu.js":"e0c5","./he":"c7aa","./he.js":"c7aa","./hi":"dc4d","./hi.js":"dc4d","./hr":"4ba9","./hr.js":"4ba9","./hu":"5b14","./hu.js":"5b14","./hy-am":"d6b6","./hy-am.js":"d6b6","./id":"5038","./id.js":"5038","./is":"0558","./is.js":"0558","./it":"6e98","./it-ch":"6f12","./it-ch.js":"6f12","./it.js":"6e98","./ja":"079e","./ja.js":"079e","./jv":"b540","./jv.js":"b540","./ka":"201b","./ka.js":"201b","./kk":"6d79","./kk.js":"6d79","./km":"e81d","./km.js":"e81d","./kn":"3e92","./kn.js":"3e92","./ko":"22f8","./ko.js":"22f8","./ku":"2421","./ku.js":"2421","./ky":"9609","./ky.js":"9609","./lb":"440c","./lb.js":"440c","./lo":"b29d","./lo.js":"b29d","./lt":"26f9","./lt.js":"26f9","./lv":"b97c","./lv.js":"b97c","./me":"293c","./me.js":"293c","./mi":"688b","./mi.js":"688b","./mk":"6909","./mk.js":"6909","./ml":"02fb","./ml.js":"02fb","./mn":"958b","./mn.js":"958b","./mr":"39bd","./mr.js":"39bd","./ms":"ebe4","./ms-my":"6403","./ms-my.js":"6403","./ms.js":"ebe4","./mt":"1b45","./mt.js":"1b45","./my":"8689","./my.js":"8689","./nb":"6ce3","./nb.js":"6ce3","./ne":"3a39","./ne.js":"3a39","./nl":"facd","./nl-be":"db29","./nl-be.js":"db29","./nl.js":"facd","./nn":"b84c","./nn.js":"b84c","./pa-in":"f3ff","./pa-in.js":"f3ff","./pl":"8d57","./pl.js":"8d57","./pt":"f260","./pt-br":"d2d4","./pt-br.js":"d2d4","./pt.js":"f260","./ro":"972c","./ro.js":"972c","./ru":"957c","./ru.js":"957c","./sd":"6784","./sd.js":"6784","./se":"ffff","./se.js":"ffff","./si":"eda5","./si.js":"eda5","./sk":"7be6","./sk.js":"7be6","./sl":"8155","./sl.js":"8155","./sq":"c8f3","./sq.js":"c8f3","./sr":"cf1e","./sr-cyrl":"13e9","./sr-cyrl.js":"13e9","./sr.js":"cf1e","./ss":"52bd","./ss.js":"52bd","./sv":"5fbd","./sv.js":"5fbd","./sw":"74dc","./sw.js":"74dc","./ta":"3de5","./ta.js":"3de5","./te":"5cbb","./te.js":"5cbb","./tet":"576c","./tet.js":"576c","./tg":"3b1b","./tg.js":"3b1b","./th":"10e8","./th.js":"10e8","./tl-ph":"0f38","./tl-ph.js":"0f38","./tlh":"cf75","./tlh.js":"cf75","./tr":"0e81","./tr.js":"0e81","./tzl":"cf51","./tzl.js":"cf51","./tzm":"c109","./tzm-latn":"b53d","./tzm-latn.js":"b53d","./tzm.js":"c109","./ug-cn":"6117","./ug-cn.js":"6117","./uk":"ada2","./uk.js":"ada2","./ur":"5294","./ur.js":"5294","./uz":"2e8c","./uz-latn":"010e","./uz-latn.js":"010e","./uz.js":"2e8c","./vi":"2921","./vi.js":"2921","./x-pseudo":"fd7e","./x-pseudo.js":"fd7e","./yo":"7f33","./yo.js":"7f33","./zh-cn":"5c3a","./zh-cn.js":"5c3a","./zh-hk":"49ab","./zh-hk.js":"49ab","./zh-tw":"90ea","./zh-tw.js":"90ea"};function n(t){var e=a(t);return s(e)}function a(t){var e=r[t];if(!(e+1)){var s=new Error("Cannot find module '"+t+"'");throw s.code="MODULE_NOT_FOUND",s}return e}n.keys=function(){return Object.keys(r)},n.resolve=a,t.exports=n,n.id="4678"},"56d7":function(t,e,s){"use strict";s.r(e);s("cadf"),s("551c"),s("f751"),s("097d");var r=s("8bbf"),n=s.n(r),a=function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",[t.proofsList.length>0?s("table",{staticClass:"data fullwidth"},[t._m(0),s("tbody",t._l(t.proofsList,function(e){return s("tr",{key:e.uid},[s("th",[t._v(t._s(e.id))]),s("td",[s("a",{attrs:{href:t._f("assetDownload")(e.uid)}},[t._v(t._s(e.asset.filename))])]),s("td",{attrs:{width:"90px"}},[s("span",{staticClass:"status",class:{white:"new"==e.status,green:"approved"==e.status,red:"rejected"==e.status}}),t._v("\n          "+t._s(t._f("capitalize")(e.status))+"\n        ")]),s("td",{attrs:{width:"220px"}},[t._v(t._s(t._f("date")(e.date)))]),s("td",[e.staffNotes?s("div",[s("strong",[t._v("Staff Notes:")]),s("br"),s("nl2br",{attrs:{tag:"p",text:e.staffNotes}})],1):t._e(),e.customerNotes?s("div",[s("strong",[t._v("Customer Notes:")]),s("br"),s("nl2br",{attrs:{tag:"p",text:e.customerNotes}})],1):t._e()])])}),0)]):t._e(),t.showProofForm?t._e():s("div",{staticClass:"btn submit",attrs:{role:"button"},on:{click:function(e){return t.onShowProofForm()}}},[t._v("Add proof")]),t.showProofForm?s("div",{staticClass:"pane"},[s("table",{staticClass:"proofform data fullwidth"},[s("tbody",[s("tr",[s("td",{attrs:{width:"200px"}},[t._t("AssetSelectInput")],2),s("td",[t._t("StaffNotesField")],2)])])]),s("div",{staticClass:"btn submit",attrs:{role:"button"},on:{click:function(e){return t.submit()}}},[t._v("Save")]),t.working?s("div",{staticClass:"spinner"}):t._e(),t.error?s("div",{staticClass:"error"},[t._v(t._s(t.error))]):t._e()]):t._e()])},o=[function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("thead",[s("tr",[s("th"),s("th",[t._v("File")]),s("th",[t._v("Status")]),s("th",[t._v("Date")]),s("th",[t._v("Notes")])])])}],c=(s("6b54"),s("cebe")),f=s.n(c),i=s("c1df"),d=s.n(i),l=s("141d"),u=s.n(l),b={name:"proofs",props:["proofs","lineItemId","source"],components:{nl2br:u.a},data:function(){return{assetSelectInput:null,working:!1,error:null,showProofForm:!1,proofsList:this.proofs}},mounted:function(){Craft.initUiElements()},filters:{date:function(t){return d()(t).format("MMMM Do YYYY, h:mm a")},assetDownload:function(t){return Craft.getActionUrl("print-shop/proofs/download",{uid:t})},capitalize:function(t){return t?(t=t.toString(),t.charAt(0).toUpperCase()+t.slice(1)):""}},methods:{onShowProofForm:function(){this.showProofForm=!0,this.$nextTick(function(){this.assetSelectInput=new Craft.AssetSelectInput({elementType:"craft\\elements\\Asset",id:"newProof-"+this.lineItemId+"-asset",limit:1,modalStorageKey:null,name:"newProof["+this.lineItemId+"][asset]",sources:[this.source]})})},submit:function(){var t=this;this.working=!0,this.error=null;var e=document.getElementById("newProof-"+this.lineItemId+"-notes"),s={lineItemId:this.lineItemId,assetIds:this.assetSelectInput.getSelectedElementIds(),staffNotes:e.value};f.a.post(Craft.getActionUrl("print-shop/proofs/save"),s,{headers:{"X-CSRF-Token":Craft.csrfTokenValue}}).then(function(e){t.working=!1,e.data.error?t.error=e.data.error:(t.proofsList.push(e.data.proof),t.showProofForm=!1)}).catch(function(e){t.working=!1,t.error=e})}}},j=b,p=(s("c5b8"),s("2877")),h=Object(p["a"])(j,a,o,!1,null,"867d231c",null),m=h.exports;s("abe2");Garnish.$doc.ready(function(){Craft.initUiElements(),window.printShop=new n.a({el:"#printshop",delimiters:["${","}"],components:{Proofs:m}})})},"8bbf":function(t,e){t.exports=Vue},abe2:function(t,e,s){},c5b8:function(t,e,s){"use strict";var r=s("239c"),n=s.n(r);n.a},cebe:function(t,e){t.exports=axios}});
//# sourceMappingURL=app.js.map