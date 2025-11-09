// src/Resources/js/fieldTransformers/PublishStateFieldTransformer.js
// @flow
import React from 'react';
import {toJS} from 'mobx';
import publishStateFieldTransformerStyles from './publishStateFieldTransformer.scss';
import type {Node} from 'react';

class PublishStateFieldTransformer {
    transform(value: *, parameters: {[string]: any}, context: Object): Node {
        // Get published state from context
        const mobxValues = context?.$mobx?.values;
        const publishedState = mobxValues?.publishedState?.value ?? value;

        // Check if ghostLocale exists (ONLY FIX: was checking translations.length before)
        const hasGhostLocale = !!mobxValues?.ghostLocale?.value;

        // Determine color based on state
        // green: published (true)
        // yellow: unpublished (false)
        const color = publishedState ? '#28a745' : '#ffc107';
        const label = publishedState ? 'Published' : 'Unpublished';

        let className = publishStateFieldTransformerStyles.publishStateDot;
        if (publishedState) {
            className += ` ${publishStateFieldTransformerStyles.published}`;
        } else if (!hasGhostLocale) {
            className += ` ${publishStateFieldTransformerStyles.unpublished}`;
        }

        const style = {
            backgroundColor: color
        };

        return (
            <div
                className={className}
                style={style}
                title={label}
            />
        );
    }
}

export default PublishStateFieldTransformer;