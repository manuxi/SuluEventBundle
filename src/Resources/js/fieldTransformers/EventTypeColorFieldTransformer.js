// @flow
import React from 'react';
import {toJS} from 'mobx';
import eventTypeColorFieldTransformerStyles from './eventTypeColorFieldTransformer.scss';
import type {Node} from 'react';

class EventTypeColorFieldTransformer {
    transform(value: *, parameters: {[string]: any}, context: Object): Node {

        const mobxValues = context?.$mobx?.values;

        const color = mobxValues?.typeColor?.value || '#ccc';
        const title = mobxValues?.typeName?.value || value;

        const style = {
            backgroundColor: color
        };

        return <div className={eventTypeColorFieldTransformerStyles.eventTypeDot} style={style} title={title} />;
    }
}

export default EventTypeColorFieldTransformer;