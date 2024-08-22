class AltumCode66pusherButton {

    /* Create and initiate the class with the proper parameters */
    constructor(options) {

        /* Initiate the main options variable */
        this.options = {};

        /* Process the passed options and the default ones */
        this.options.content = options.content || '';
        this.options.button = options.button || '';
        this.options.subscribed_success_url = typeof options.subscribed_success_url === 'undefined' ? null : options.subscribed_success_url;

        /* On what pages to show the notification */
        this.options.trigger_all_pages = typeof options.trigger_all_pages === 'undefined' ? true : options.trigger_all_pages;
        this.options.triggers = options.triggers || [];

        /* More checks on if it should be displayed */
        this.options.display_mobile = typeof options.display_mobile === 'undefined' ? true : options.display_mobile;
        this.options.display_desktop = typeof options.display_desktop === 'undefined' ? true : options.display_desktop;

        /* Animations */
        this.options.on_animation = typeof options.on_animation === 'undefined' ? 'fadeIn' : options.on_animation;
        this.options.off_animation = typeof options.off_animation === 'undefined' ? 'fadeOut' : options.off_animation;
    }

    /* Function to build the toast element */
    async build() {

        /* Process triggers */
        if(!this.options.trigger_all_pages) {
            let triggered = this.is_page_triggered(this.options.triggers);

            if(!triggered) {
                return false;
            }
        }


        /* Check if it should be shown on the current screen */
        if((!this.options.display_mobile && window.innerWidth < 768) || (!this.options.display_desktop && window.innerWidth > 768)) {
            return false;
        }

        /* Create the html element */
        let main_element = document.createElement('div');
        main_element.className = 'altumcode-66pusher-button';

        /* Add the content to the element */
        main_element.innerHTML = this.options.content;

        /* Add the proper events to the primary button */
        let wrapper = main_element.querySelector('[data-wrapper]');

        let process_and_display_successful_subscription = () => {
            /* Display a success message */
            main_element.querySelector('[data-title]').innerHTML = this.options.button.subscribed_title;
            main_element.querySelector('[data-description]').innerHTML = this.options.button.subscribed_description;

            if(this.options.button.subscribed_image_url) {
                main_element.querySelector('[data-image]').src = this.options.button.subscribed_image_url;
                main_element.querySelector('[data-image]').alt = this.options.button.subscribed_image_alt;
            }

            /* Redirect if needed */
            setTimeout(() => {
                if(this.options.subscribed_success_url) {
                    /* Redirect if needed */
                    window.location.href = this.options.subscribed_success_url;
                }

                display_unsubscribe();
            }, 4000);
        }

        let process_and_display_successful_unsubscription = () => {
            /* Display a success message */
            main_element.querySelector('[data-title]').innerHTML = this.options.button.unsubscribed_title;
            main_element.querySelector('[data-description]').innerHTML = this.options.button.unsubscribed_description;

            if(this.options.button.unsubscribed_image_url) {
                main_element.querySelector('[data-image]').src = this.options.button.unsubscribed_image_url;
                main_element.querySelector('[data-image]').alt = this.options.button.unsubscribed_image_alt;
            }

            /* Redirect if needed */
            setTimeout(() => {
                if(this.options.unsubscribed_success_url) {
                    /* Redirect if needed */
                    window.location.href = this.options.unsubscribed_success_url;
                }

                display_subscribe();
            }, 4000);
        }

        let display_unsubscribe = () => {
            /* Display a success message */
            main_element.querySelector('[data-title]').innerHTML = this.options.button.unsubscribe_title;
            main_element.querySelector('[data-description]').innerHTML = this.options.button.unsubscribe_description;

            if(this.options.button.unsubscribe_image_url) {
                main_element.querySelector('[data-image]').src = this.options.button.unsubscribe_image_url;
                main_element.querySelector('[data-image]').alt = this.options.button.unsubscribe_image_alt;
            }
        }

        let display_subscribe = () => {
            /* Display a success message */
            main_element.querySelector('[data-title]').innerHTML = this.options.button.title;
            main_element.querySelector('[data-description]').innerHTML = this.options.button.description;

            if(this.options.button.image_url) {
                main_element.querySelector('[data-image]').src = this.options.button.image_url;
                main_element.querySelector('[data-image]').alt = this.options.button.image_alt;
            }
        }

        let process_and_display_denied_subscription = () => {
            /* Button now a refresh button */
            wrapper.addEventListener('click', () => {
                location.reload();
            })

            main_element.querySelector('[data-title]').innerHTML = this.options.button.permission_denied_title;
            main_element.querySelector('[data-description]').innerHTML = this.options.button.permission_denied_description;
            if(this.options.button.permission_denied_image_url) {
                main_element.querySelector('[data-image]').src = this.options.button.permission_denied_image_url;
                main_element.querySelector('[data-image]').alt = this.options.button.permission_denied_image_alt;
            }
        }


        if(await get_notification_permission() == 'denied') {

            process_and_display_denied_subscription()

        } else {

            /* Check the current status of the user */
            let is_subscribed = await get_subscription_status();

            if(is_subscribed) {
                display_unsubscribe();
            } else {
                display_subscribe();
            }

            /* Processor fo the click */
            wrapper.addEventListener('click', async event => {

                /* Enable loading animation */
                main_element.querySelector('[data-loading]').style.display = 'flex';

                /* Unsubscribe */
                if(await get_subscription_status()) {
                    let has_unsubscribed = await unsubscribe();

                    if(has_unsubscribed) {
                        process_and_display_successful_unsubscription();
                    }
                }

                /* Subscribe */
                else {
                    let has_subscribed = await request_push_notification_permission_and_subscribe(event);

                    if(has_subscribed) {
                        process_and_display_successful_subscription();
                    } else {
                        process_and_display_denied_subscription();
                    }
                }

                /* Disable loading animation */
                main_element.querySelector('[data-loading]').style.display = 'none';

            });

        }

        return main_element;

    }

    /* Function to make sure that the content of the site has loaded before building beginning the main process */
    initiate() {

        let wait_for_css_and_process = () => {
            let interval = null;

            interval = setInterval(() => {

                if(pixel_css_loaded) {
                    clearInterval(interval);

                    this.process();
                }

            }, 100);

        };

        if(document.readyState === 'complete' || (document.readyState !== 'loading' && !document.documentElement.doScroll)) {
            wait_for_css_and_process();
        } else {
            document.addEventListener('DOMContentLoaded', () => {
                wait_for_css_and_process()
            });
        }

        /* Check for url changes for ajax based contents that change the url dynamically */
        let current_page = location.href;

        setInterval(() => {
            if(current_page != location.href) {
                current_page = location.href;

                wait_for_css_and_process();
            }
        }, 750);

    }

    /* Display main function */
    async process() {

        let main_element = await this.build();

        /* Make sure we have an element to display */
        if(!main_element) return false;

        /* Search for the right tag */
        if(document.querySelector(`[data-${pixel_exposed_identifier}-button]`)) {
            document.querySelector(`[data-${pixel_exposed_identifier}-button]`).classList.add('altumcode-66pusher-button-inline-block');
            document.querySelector(`[data-${pixel_exposed_identifier}-button]`).appendChild(main_element);
        }

    }

    is_page_triggered(triggers) {
        let triggered = false;

        /* If there is a Not type of condition, make sure to start with the triggered state of true */
        for(let trigger of triggers) {
            if(trigger.type.startsWith('not_')) {
                triggered = true;
                break;
            }
        }


        triggers.forEach(trigger => {

            switch(trigger.type) {
                case 'exact':

                    if(trigger.value == window.location.href) {
                        triggered = true;
                    }

                    break;

                case 'not_exact':

                    if(trigger.value == window.location.href) {
                        triggered = false;
                    }

                    break;

                case 'contains':

                    if(window.location.href.includes(trigger.value)) {
                        triggered = true;
                    }

                    break;

                case 'not_contains':

                    if(window.location.href.includes(trigger.value)) {
                        triggered = false;
                    }

                    break;

                case 'starts_with':

                    if(window.location.href.startsWith(trigger.value)) {
                        triggered = true;
                    }

                    break;

                case 'not_starts_with':

                    if(window.location.href.startsWith(trigger.value)) {
                        triggered = false;
                    }

                    break;

                case 'ends_with':

                    if(window.location.href.endsWith(trigger.value)) {
                        triggered = true;
                    }

                    break;

                case 'not_ends_with':

                    if(window.location.href.endsWith(trigger.value)) {
                        triggered = false;
                    }

                    break;

                case 'page_contains':

                    if(document.body.innerText.includes(trigger.value)) {
                        triggered = true;
                    }

                    break;
            }

        });

        return triggered;
    }

}
