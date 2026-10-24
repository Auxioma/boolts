import { Controller } from '@hotwired/stimulus';
import { ShaderFill } from '../../lib/shader-fill.js';
import * as movingGradient from '../../lib/custom-effects/CodeComponentId_8ce92017e53431a2f04b3574f4ba7c98f6f55f1e_7037.js';
import * as concentricPatterns from '../../lib/custom-effects/CodeComponentId_c2b302fbdfce8a1ec92a5a80b10a5916ab506ed7_615.js';
import * as patternGrid from '../../lib/custom-effects/CodeComponentId_ce59f555a29a71115f0add8ed63800fd022d0b22_618.js';

/**
 * Remplissage shader WebGPU exporté depuis Figma, posé sur un <canvas>.
 *
 * Usage :
 *   <canvas data-controller="home--shader-fill"
 *           data-home--shader-fill-effect-value="moving-gradient"
 *           data-home--shader-fill-params-value="{{ {...}|json_encode }}"></canvas>
 *
 * Sans WebGPU, le canvas est retiré et le fond CSS du parent reste visible.
 */
const EFFECTS = {
    'moving-gradient': movingGradient,
    'concentric-patterns': concentricPatterns,
    'pattern-grid': patternGrid,
};

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        effect: String,
        params: Object,
    };

    connect() {
        const effect = EFFECTS[this.effectValue];
        if (!effect) {
            console.warn(`Unknown shader effect "${this.effectValue}"`);
            return;
        }

        this.fill = new ShaderFill(this.element, {
            setup: effect.setup,
            render: effect.render,
            manifest: effect.manifest,
            params: this.paramsValue,
        });

        this.fill.mount().catch((error) => {
            console.warn('Failed to initialize shader runtime', error);
            this.element.hidden = true;
        });
    }

    disconnect() {
        this.fill?.destroy();
        this.fill = null;
    }
}
