import EventUtil from '@hundh/contao-utils-bundle/js/event-util';
import AjaxUtil from '@hundh/contao-utils-bundle/js/ajax-util';

class FilterBundle {

    static init() {
        FilterBundle.registerEvents();
        FilterBundle.initProximitySearch();
    }

    static initProximitySearch() {
        let proximitySearchFields = document.querySelectorAll('form fieldset.proximity_search > div');

        if (proximitySearchFields.length < 1) {
            return;
        }

        proximitySearchFields.forEach((proximitySearch) => {
            let config = JSON.parse(proximitySearch.getAttribute('data-proximity-search-config')),
                useLocation = proximitySearch.querySelector('[name="' + config.useCurrentLocationField + '"]');

            if (useLocation !== null) {
                let locationHidden = proximitySearch.querySelector('[name="' + config.currentLocationHiddenField + '"]');

                useLocation.addEventListener('click', () => {
                    if (useLocation.checked) {
                        navigator.geolocation.getCurrentPosition(function(position) {
                            locationHidden.value = position.coords.latitude + ',' + position.coords.longitude;
                        }, () => {
                            locationHidden.value = '';
                        });
                    } else {
                        locationHidden.value = '';
                    }
                });
            }
        })
    }

    static registerEvents() {
        document.addEventListener('filterAsyncSubmit', function(event) {
            event.preventDefault();
            FilterBundle.asyncSubmit(event.detail.form);
        });

        EventUtil.addDynamicEventListener('click', '.mod_filter form [data-submit-on-change] input[type="radio"][value=""], .mod_filter form [data-submit-on-change] input[type="checkbox"][value=""]', function(element, event) {
            FilterBundle.resetRadioAndCheckboxField(element);
        });

        EventUtil.addDynamicEventListener('change',
            '.mod_filter form[data-async] input[data-submit-on-change], .mod_filter form[data-async] select[data-submit-on-change], .mod_filter form[data-async] [data-submit-on-change] input',
            function(element, event) {
                event.preventDefault();

                FilterBundle.initAsyncFormSubmit(element);
            });

        EventUtil.addDynamicEventListener('click', '.mod_filter form[data-async] button[type="submit"]',
            function(element, event) {
                event.preventDefault();
                FilterBundle.asyncSubmit(element.form, element);
            });

        FilterBundle.initAsyncSubmitOnInput();
    }

    static initAsyncSubmitOnInput() {
        let timeout;

        EventUtil.addDynamicEventListener('input',
            '.mod_filter form[data-async] input[data-submit-on-input], .mod_filter form[data-async] [data-submit-on-input] input',
            function(element, event) {
                event.preventDefault();

                if(element.dataset.threshold > element.value.length) {
                    return;
                }

                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    element.form.classList.add('keep-form');
                    FilterBundle.initAsyncFormSubmit(element);
                }, element.dataset.debounce);
            });

        EventUtil.addDynamicEventListener('focusout',
            '.mod_filter form[data-async] input[data-submit-on-input], .mod_filter form[data-async] [data-submit-on-input] input',
            function(element, event) {
                element.form.classList.remove('keep-form');
            });
    }


    static initAsyncFormSubmit(element) {
        let clickedButton = document.createElement('div');

        clickedButton.setAttribute('name', element.form.name + '[submit]') ;

        FilterBundle.asyncSubmit(element.form, clickedButton);
    }

    static asyncSubmit(form, clickedButton = null) {
        let method = form.getAttribute('method'),
            action = form.getAttribute('action'),
            data = FilterBundle.getData(form),
            config = FilterBundle.getConfig(form);

        if (clickedButton !== null) {
            data.append(clickedButton.getAttribute('name'), '');
        }

        if ('get' === method || 'GET' === method) {
            AjaxUtil.get(action, data, config);
        } else {
            AjaxUtil.post(action, data, config);
        }
    }

    static getConfig(form) {
        return {
            onSuccess: FilterBundle.onSuccess,
            beforeSubmit: FilterBundle.beforeSubmit,
            afterSubmit: FilterBundle.afterSubmit,
            form: form
        };
    }

    static onSuccess(request) {
        let response = 'undefined' !== request.response ? JSON.parse(request.response) : null;

        if (null === response) {
            return;
        }

        if ('undefined' === response.filterName) {
            console.log('Error', 'Es wurde kein Filtername gesetzt.');
            return;
        }

        if ('undefined' === response.filter) {
            console.log('Error', 'Es wurde kein Filter zurück geliefert.');
            return;
        }

        let form = document.querySelector('form[name="' + response.filterName + '"]');

        if(!form.classList.contains('keep-form')) {
            FilterBundle.replaceFilterForm(form, response.filter);
        }

        form.setAttribute('data-response', request.response);
        form.setAttribute('data-submit-success', 1);

        form.dispatchEvent(new CustomEvent('filterAjaxComplete', {detail: form, bubbles: true, cancelable: true}));
    }

    static beforeSubmit(url, data, config) {
        let form = config.form,
            list = document.querySelector(form.getAttribute('data-list'));

        form.setAttribute('data-submit-success', 0);
        form.setAttribute('data-response', '');
        form.querySelectorAll('input:not(.disabled):not([data-submit-on-input="1"]):not([type="hidden"]), button[type="submit"]').forEach((elem) => {
            elem.disabled = true;
        });

        form.classList.add('submitting');

        if (null !== list) {
            list.classList.add('updating');
        }
    }

    static afterSubmit(url, data, config) {
        let form = config.form;

        form.querySelectorAll('[disabled]').forEach((elem) => {
            elem.disabled = false;
        });

        form.classList.remove('submitting');
    }

    static getData(form) {
        let formData = new FormData(form);
        formData.append('filterName', form.getAttribute('name'));

        return formData;
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
    }

    static resetRadioAndCheckboxField(element) {
        let parent = element.closest('[data-choices]'),
            form = element.closest('form'),
            checked = parent.querySelectorAll('input:checked');

        checked.forEach((elem) => {
            elem.checked = false;
        });

        FilterBundle.asyncSubmit(form);
    }
}

export {FilterBundle};
