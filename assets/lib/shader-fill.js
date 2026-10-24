/*
 * Runtime des remplissages shader exportés depuis Figma (<ShaderFill>).
 *
 * Portage en JavaScript sans React du runtime Figma (ShaderFill.tsx,
 * useShaderRuntime.ts, webgpu.ts) : seul le chemin « fill procédural » est
 * repris, sans contenu DOM à rastériser (pas besoin de HTML-in-Canvas).
 * Les modules de shaders eux-mêmes sont dans ./custom-effects/ et sont
 * copiés tels quels depuis Figma.
 */

const DEFAULT_MANIFEST = {
    version: 2,
    isAnimated: false,
    usesMouse: false,
    name: '',
};

const TEXTURE_FORMAT = 'rgba8unorm';

// Un seul GPUDevice partagé par tous les canvas de la page.
let devicePromise = null;

function acquireDevice() {
    if (!devicePromise) {
        devicePromise = (async () => {
            const gpu = navigator.gpu;
            if (!gpu) {
                throw new Error('WebGPU unsupported.');
            }
            const adapter = await gpu.requestAdapter();
            if (!adapter) {
                throw new Error('Failed to get WebGPU adapter.');
            }
            return adapter.requestDevice();
        })();
        devicePromise.catch(() => {
            devicePromise = null;
        });
    }

    return devicePromise;
}

export class ShaderFill {
    constructor(canvas, shader) {
        this.canvas = canvas;
        this.shader = shader;
        this.manifest = shader.manifest ?? DEFAULT_MANIFEST;
        this.state = {};
        this.input = null;
        this.output = null;
        this.time = 0;
        this.deltaTime = 0;
        this.frame = 0;
        this.mousePosition = { x: 0, y: 0, down: false };
        this.raf = null;
        this.lastTs = null;
        this.visible = true;
        this.destroyed = false;
    }

    async mount() {
        const device = await acquireDevice();
        if (this.destroyed) {
            return;
        }

        const context = this.canvas.getContext('webgpu');
        if (!context) {
            throw new Error('Failed to get WebGPU context.');
        }
        context.configure({ device, format: TEXTURE_FORMAT, alphaMode: 'premultiplied' });

        this.gpu = { device, context, format: TEXTURE_FORMAT };

        const rect = this.canvas.getBoundingClientRect();
        this.resize(rect.width, rect.height);

        this.shader.setup(device, this.frameState());
        this.ready = true;
        this.paint();

        // Garde la grille du canvas nette quand la taille affichée change.
        this.resizeObserver = new ResizeObserver((entries) => {
            for (const entry of entries) {
                const size = entry.contentBoxSize?.[0];
                this.resize(size?.inlineSize ?? entry.contentRect.width, size?.blockSize ?? entry.contentRect.height);
            }
            this.paint();
        });
        this.resizeObserver.observe(this.canvas, { box: 'content-box' });

        // Inutile d'animer un shader hors écran (slides clonées par Splide, etc.).
        this.intersectionObserver = new IntersectionObserver((entries) => {
            this.visible = entries.some((entry) => entry.isIntersecting);
            if (this.visible) {
                this.paint();
            } else {
                this.stopClock();
            }
        });
        this.intersectionObserver.observe(this.canvas);
    }

    resize(width, height) {
        const renderScale = window.devicePixelRatio || 1;
        this.canvas.width = Math.max(Math.round(width * renderScale), 1);
        this.canvas.height = Math.max(Math.round(height * renderScale), 1);

        this.input?.destroy();
        this.input = this.gpu.device.createTexture({
            size: [this.canvas.width, this.canvas.height, 1],
            format: TEXTURE_FORMAT,
            usage: GPUTextureUsage.TEXTURE_BINDING | GPUTextureUsage.COPY_DST | GPUTextureUsage.RENDER_ATTACHMENT,
        });
    }

    frameState() {
        return {
            gpu: this.gpu,
            input: this.input,
            output: this.output,
            time: this.time,
            deltaTime: this.deltaTime,
            frame: this.frame,
            mousePosition: this.mousePosition,
            renderScale: window.devicePixelRatio || 1,
            params: this.shader.params,
            state: this.state,
        };
    }

    paint() {
        if (!this.ready || this.destroyed) {
            return;
        }

        try {
            this.output = this.gpu.context.getCurrentTexture();
            this.shader.render(this.gpu.device, this.frameState());
        } catch (error) {
            console.warn('Failed to render effect', error);
            return;
        }

        if (this.manifest.isAnimated && this.visible) {
            this.startClock();
        } else {
            this.stopClock();
        }
    }

    startClock() {
        if (this.raf !== null) {
            return;
        }
        this.lastTs = null;

        const tick = (ts) => {
            if (this.destroyed) {
                this.raf = null;
                return;
            }
            this.raf = requestAnimationFrame(tick);
            this.deltaTime = this.lastTs === null ? 0 : ts - this.lastTs;
            this.lastTs = ts;
            this.time += this.deltaTime;
            this.frame += 1;
            this.paint();
        };
        this.raf = requestAnimationFrame(tick);
    }

    stopClock() {
        if (this.raf !== null) {
            cancelAnimationFrame(this.raf);
            this.raf = null;
        }
        this.lastTs = null;
    }

    destroy() {
        this.destroyed = true;
        this.stopClock();
        this.resizeObserver?.disconnect();
        this.intersectionObserver?.disconnect();
        this.input?.destroy();
        this.input = null;
    }
}
