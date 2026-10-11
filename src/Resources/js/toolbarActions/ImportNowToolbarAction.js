// @flow
import {action, observable, reaction} from 'mobx';
import {AbstractFormToolbarAction} from 'sulu-admin-bundle/views';
import {ResourceRequester} from 'sulu-admin-bundle/services';
import {translate} from 'sulu-admin-bundle/utils';

const POLL_INTERVAL = 4000;
// a run that takes longer is taken for dead on the server as well (ImportRunner::STALE_AFTER)
const POLL_LIMIT = 1800000;

/**
 * The button "Import now" of the tab "Import" in the settings of the events: starts the import of all sources in the
 * background (PUT event-settings?action=import) and looks every few seconds whether it is done. The form shows the state
 * (importRunning, importRunningText) and, after the run, its result.
 */
export default class ImportNowToolbarAction extends AbstractFormToolbarAction {
    @observable starting: boolean = false;
    timer: ?TimeoutID = null;
    waitedSince: number = 0;

    constructor(...args: Array<any>) {
        super(...args);

        // a run that was started before the tab was opened (or by the daily job, with the button of another browser): look
        // until it is done
        this.disposer = reaction(
            () => this.resourceFormStore.data.importRunning,
            (running) => {
                if (true === running && !this.timer) {
                    this.waitedSince = Date.now();
                    this.poll();
                }
            },
            {fireImmediately: true}
        );
    }

    disposer: ?Function = null;

    getToolbarItemConfig() {
        const running = this.starting || true === this.resourceFormStore.data.importRunning;

        return {
            type: 'button',
            icon: 'su-process',
            label: translate('sulu_event.import.import_now'),
            disabled: running,
            loading: running,
            onClick: this.handleClick,
        };
    }

    @action handleClick = () => {
        this.starting = true;

        ResourceRequester.put('event_settings', {}, {id: 1, action: 'import'})
            .then(action(() => {
                this.form.showSuccessSnackbar();
                this.waitedSince = Date.now();
                // the form shows "running" from now on; the button stays disabled until it does
                this.resourceFormStore.resourceStore.reload();
                setTimeout(action(() => {
                    this.starting = false;
                    this.poll();
                }), 1500);
            }))
            .catch(action((error) => {
                this.starting = false;
                this.form.errors.push(error);
            }));
    };

    poll = () => {
        this.timer = setTimeout(() => {
            this.resourceFormStore.resourceStore.reload();
            // the data arrives a moment after the request: look then
            this.timer = setTimeout(() => {
                if (true === this.resourceFormStore.data.importRunning && Date.now() - this.waitedSince < POLL_LIMIT) {
                    this.poll();
                } else {
                    this.timer = null;
                }
            }, 1500);
        }, POLL_INTERVAL);
    };

    destroy() {
        if (this.timer) {
            clearTimeout(this.timer);
        }
        if (this.disposer) {
            this.disposer();
        }
        super.destroy();
    }
}
