!function(e){var t={};function n(r){if(t[r])return t[r].exports;var i=t[r]={i:r,l:!1,exports:{}};return e[r].call(i.exports,i,i.exports,n),i.l=!0,i.exports}n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var i in e)n.d(r,i,function(t){return e[t]}.bind(null,i));return r},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="/public/js/",n(n.s="Xv8R")}({"5r56":function(e,t){e.exports=utilsBundle},Xv8R:function(e,t,n){"use strict";n.r(t);var r=n("g0aJ");document.addEventListener("DOMContentLoaded",r.a.init)},g0aJ:function(module,__webpack_exports__,__webpack_require__){"use strict";__webpack_require__.d(__webpack_exports__,"a",function(){return FilterBundle});var _hundh_contao_utils_bundle__WEBPACK_IMPORTED_MODULE_0__=__webpack_require__("5r56"),_hundh_contao_utils_bundle__WEBPACK_IMPORTED_MODULE_0___default=__webpack_require__.n(_hundh_contao_utils_bundle__WEBPACK_IMPORTED_MODULE_0__);function _classCallCheck(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function _defineProperties(e,t){for(var n=0;n<t.length;n++){var r=t[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,r.key,r)}}function _createClass(e,t,n){return t&&_defineProperties(e.prototype,t),n&&_defineProperties(e,n),e}var FilterBundle=function(){function FilterBundle(){_classCallCheck(this,FilterBundle)}return _createClass(FilterBundle,null,[{key:"init",value:function(){FilterBundle.registerEvents()}},{key:"registerEvents",value:function(){document.addEventListener("filterAsyncSubmit",function(e){e.preventDefault(),FilterBundle.asyncSubmit(e.detail.form)}),utilsBundle.event.addDynamicEventListener("click",'.mod_filter form [data-submit-on-change] input[type="radio"][value=""], .mod_filter form [data-submit-on-change] input[type="checkbox"][value=""]',function(e,t){FilterBundle.resetRadioAndCheckboxField(e)}),utilsBundle.event.addDynamicEventListener("change",".mod_filter form[data-async] input[data-submit-on-change], .mod_filter form[data-async] [data-submit-on-change] input",function(e,t){t.preventDefault(),FilterBundle.asyncSubmit(e.form)}),utilsBundle.event.addDynamicEventListener("click",'.mod_filter form[data-async] button[type="submit"]',function(e,t){t.preventDefault(),FilterBundle.asyncSubmit(e.form)})}},{key:"asyncSubmit",value:function(e){var t=e.getAttribute("method"),n=e.getAttribute("action"),r=FilterBundle.getData(e),i=FilterBundle.getConfig(e);"get"===t||"GET"===t?utilsBundle.ajax.get(n,r,i):utilsBundle.ajax.post(n,r,i)}},{key:"getConfig",value:function(e){return{onSuccess:FilterBundle.onSuccess,beforeSubmit:FilterBundle.beforeSubmit,afterSubmit:FilterBundle.afterSubmit,form:e,headers:FilterBundle.getRequestHeaders(e.getAttribute("method"))}}},{key:"getRequestHeaders",value:function(e){return"get"===e||"GET"===e?{"X-Requested-With":"XMLHttpRequest"}:{"X-Requested-With":"XMLHttpRequest","Content-Type":"application/x-www-form-urlencoded; charset=UTF-8\n"}}},{key:"onSuccess",value:function(e){var t="undefined"!==e.response?JSON.parse(e.response):null;if(null!==t)if("undefined"!==t.filterName)if("undefined"!==t.filter){var n=document.querySelector('form[name="'+t.filterName+'"]');FilterBundle.replaceFilterForm(n,t.filter),n.setAttribute("data-response",e.response),n.setAttribute("data-submit-success",1),n.dispatchEvent(new CustomEvent("filterAjaxComplete",{detail:n,bubbles:!0,cancelable:!0}))}else console.log("Error","Es wurde kein Filter zurück geliefert.");else console.log("Error","Es wurde kein Filtername gesetzt.")}},{key:"beforeSubmit",value:function(e,t,n){var r=n.form,i=document.querySelector(r.getAttribute("data-list"));r.setAttribute("data-submit-success",0),r.setAttribute("data-response",""),r.querySelectorAll('input:not(.disabled), button[type="submit"]').forEach(function(e){e.disabled=!0}),r.classList.add("submitting"),i.classList.add("updating")}},{key:"afterSubmit",value:function(e,t,n){var r=n.form;r.querySelectorAll("[disabled]").forEach(function(e){e.disabled=!1}),r.classList.remove("submitting")}},{key:"getData",value:function(e){var t={};return e.querySelectorAll('input:checked, input[type="hidden"], input[type="text"]:not([value=""])').forEach(function(e){""!==e.value&&(t[e.name]=e.value)}),t.filterName=e.getAttribute("name"),t}},{key:"replaceFilterForm",value:function replaceFilterForm(form,filter){form.innerHTML=filter,form.querySelectorAll("script").forEach(function(script){try{eval(script.innerHTML||script.innerText)}catch(e){}})}},{key:"resetRadioAndCheckboxField",value:function(e){var t=e.closest("[data-choices]"),n=e.closest("form");t.querySelectorAll("input:checked").forEach(function(e){e.checked=!1}),FilterBundle.asyncSubmit(n)}}]),FilterBundle}()}});