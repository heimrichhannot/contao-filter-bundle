import '@hundh/contao-utils-bundle';

class FilterBundle {

  static init() {
    FilterBundle.registerEvents();
  }


  static registerEvents() {
    utilsBundle.event.addDynamicEventListener('change', '.mod_filter form[data-async] input[data-submit-on-change], .mod_filter form[data-async] [data-submit-on-change] input', function(element, event) {
      event.preventDefault();
      FilterBundle.asyncSubmit(element.form);
    });

    utilsBundle.event.addDynamicEventListener('click', '.mod_filter form[data-async] button[type="submit"]', function(element, event) {
      event.preventDefault();
      FilterBundle.asyncSubmit(element.form);
    });
  }


  static asyncSubmit(form) {
    let request = new XMLHttpRequest();

    request.onreadystatechange = function() {
      const DONE = 4;
      const OK = 200;

      if (request.readyState === DONE) {
        if (request.status === OK) {
          let response = JSON.parse(request.response);

          if (response.filter) {
            FilterBundle.replaceFilterForm(form, response.filter);
          }

          form.setAttribute('data-response', request.response);
        }
      }
    }.bind(this);

    FilterBundle.beforeRequest(form);
    FilterBundle.doAsyncSubmit(form, request);
    FilterBundle.afterRequest(form);
  }


  static beforeRequest(form) {
    form.querySelectorAll('input:not(.disabled), button[type="submit"]').forEach((elem) => {
      elem.disabled = true;
    });

    form.classList.add('submitting');
  }


  static afterRequest(form) {
    form.querySelectorAll('[disabled]').forEach((elem) => {
      elem.disabled = false;
    });

    form.classList.remove('submitting');
    form.setAttribute('data-submit-success', 1);
  }


  static doAsyncSubmit(form, request) {
    let method   = form.getAttribute('method'),
        action   = form.getAttribute('action');

    if ('get' === method) {
      FilterBundle.doAsyncGetSubmit(request, action, method, FilterBundle.getGetData(form));
    }
    else {
      FilterBundle.doAsyncPostSubmit(request, action, method, FilterBundle.getPostData(form));

    }
  }


  static doAsyncGetSubmit(request, action, method, data) {
    action += '?' + data;
    request.open(method, action, true);
    request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    request.send(null);
  }


  static doAsyncPostSubmit(request, action, method, data) {
    request.open(method, action, true);
    request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8\n');
    request.send(data);
  }


  static getGetData(form) {
    let selected = form.querySelectorAll('input:checked, input[type="hidden"]'),
        query = '';

    selected.forEach((elem) => {
      if('' !== query) {
        query += '&';
      }

      query += elem.name + '=' + elem.value;
    });

    return query;
  }


  static getPostData(form) {
    let selected = form.querySelectorAll('input:checked, input[type="hidden"]'),
        parameter = '';

    selected.forEach((elem) => {
      if('' !== parameter) {
        parameter += '&';
      }

      parameter += elem.name + '=' + elem.value;
    });

    return parameter;
  }


  static replaceFilterForm(form, filter) {
    form.innerHTML = filter;

    // run embedded js code (example contao captcha field)
    form.querySelectorAll('script').forEach(script => {
      try {
        eval(script.innerHTML || script.innerText);
      } catch (e) {

      }
    });

    form.dispatchEvent(new CustomEvent('formhybrid_ajax_complete', {
      bubbles: true,
    }));
  }
}

export {FilterBundle};
