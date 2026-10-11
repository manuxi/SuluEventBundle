// @flow
import formToolbarActionRegistry from 'sulu-admin-bundle/views/Form/registries/formToolbarActionRegistry';
import ImportNowToolbarAction from './toolbarActions/ImportNowToolbarAction';

export {default as EventTypeColorFieldTransformer} from './fieldTransformers/EventTypeColorFieldTransformer';
export {ImportNowToolbarAction};

// The button "Import now" of the tab "Import" (sulu_event.import.admin_button: true)
formToolbarActionRegistry.add('sulu_event.import_now', ImportNowToolbarAction);
