
// Property metadata is only used in Figma Design, so this is a no-op.
function defineProperties(
  _component,
  _properties,
) {}

export default function Effect() { }
export function setup(device, frame) {
    var wgsl = `diagnostic(off,derivative_uniformity);

struct Uniforms {
  pattern: vec4f,
  color: vec4f,
  transform: vec4f,
  geometry: vec4f,
};

@group(0) @binding(0) var<uniform> u: Uniforms;

struct VsIn { @location(0) pos: vec2f, @location(1) uv: vec2f, };
struct VsOut { @builtin(position) position: vec4f, @location(0) uv: vec2f, };

fn shapeDistance(p: vec2f, shape: i32) -> f32 {
  if (shape == 0) { return max(p.y, (sqrt(3.0) * abs(p.x) - p.y) * 0.5); }
  if (shape == 1) { return length(p); }
  return max(abs(p.x), abs(p.y));
}

@vertex fn vs_main(in: VsIn) -> VsOut {
  var out: VsOut;
  out.position = vec4f(in.pos, 0.0, 1.0);
  out.uv = in.uv;
  return out;
}

@fragment fn fs_main(in: VsOut) -> @location(0) vec4f {
  let shape = i32(u.pattern.x + 0.5);
  let ringCount = u.pattern.y;
  let falloff = u.pattern.z;
  let inverseFalloff = u.pattern.w >= 0.5;
  let color = u.color;
  let zoom = max(u.transform.x, 0.0001);
  let cosine = u.transform.y;
  let sine = u.transform.z;
  let offset = u.transform.w;
  let aspect = u.geometry.x;
  let centerUv = u.geometry.yz / 100.0;
  var position = (in.uv - centerUv) / zoom;
  position.x *= aspect;
  let rotatedPosition = vec2f(cosine * position.x - sine * position.y, sine * position.x + cosine * position.y);
  let distance = shapeDistance(rotatedPosition, shape);
  let scaledDistance = distance * ringCount;
  let ringPosition = fract(scaledDistance + offset);
  let blendWidth = min(fwidth(scaledDistance) * 1.5, 0.2499);
  let leadingEdge = smoothstep(0.0, blendWidth, ringPosition);
  let trailingEdge = smoothstep(0.5 - blendWidth, 0.5, ringPosition);
  let band = leadingEdge * (1.0 - trailingEdge);
  let normalFalloff = exp(-distance * falloff);
  var fade = normalFalloff;
  if (inverseFalloff) { fade = 1.0 - normalFalloff; }
  let alpha = band * fade * color.a;
  return vec4f(color.rgb, alpha);
}
`;
    frame.state.module = device.createShaderModule({ code: wgsl });
    frame.state.pipeline = null;
    frame.state.pipelineFormat = null;
    frame.state.bindGroup = null;
    frame.state.quad = device.createBuffer({ size: 6 * 4 * 4, usage: GPUBufferUsage.VERTEX, mappedAtCreation: true });
    new Float32Array(frame.state.quad.getMappedRange()).set([
        -1, -1, 0, 1, 1, -1, 1, 1, -1, 1, 0, 0,
        -1, 1, 0, 0, 1, -1, 1, 1, 1, 1, 1, 0,
    ]);
    frame.state.quad.unmap();
    frame.state.uniformBuf = device.createBuffer({ size: 64, usage: GPUBufferUsage.UNIFORM | GPUBufferUsage.COPY_DST });
    frame.state.uniformData = new Float32Array(16);
}
export function render(device, frame) {
    var params = frame.params || {};
    function finiteNumber(value, fallback) { var number = Number(value); return Number.isFinite(number) ? number : fallback; }
    function clampedNumber(name, fallback, min, max) { var value = finiteNumber(params[name], fallback); return Math.min(max, Math.max(min, value)); }
    function booleanParam(name, fallback) { if (typeof params[name] === "boolean")
        return params[name] ? 1 : 0; return fallback ? 1 : 0; }
    function selectParam(name, count, fallback) { var value = Math.round(Number(params[name])); return Number.isFinite(value) && value >= 0 && value < count ? value : fallback; }
    function colorParam(name, fallback) {
        var value = params[name] || {};
        return [
            Math.min(1, Math.max(0, finiteNumber(value.r, fallback[0]))),
            Math.min(1, Math.max(0, finiteNumber(value.g, fallback[1]))),
            Math.min(1, Math.max(0, finiteNumber(value.b, fallback[2]))),
            Math.min(1, Math.max(0, finiteNumber(value.a, fallback[3]))),
        ];
    }
    function transformParam(name, fallback) {
        var value = params[name] || {};
        return { x: finiteNumber(value.x, fallback.x), y: finiteNumber(value.y, fallback.y), radius: Math.min(250, Math.max(0.1, finiteNumber(value.radius, fallback.radius))), angle: finiteNumber(value.angle, fallback.angle) };
    }
    var width = Math.max(1, finiteNumber(frame.output.width, 1));
    var height = Math.max(1, finiteNumber(frame.output.height, 1));
    var aspect = width / height;
    var transform = transformParam("transform", { x: 50, y: 50, radius: 50, angle: 0 });
    var zoom = transform.radius / 50;
    var rotation = -transform.angle * Math.PI / 180;
    var cosine = Math.cos(rotation);
    var sine = Math.sin(rotation);
    var color = colorParam("color", [1, 1, 1, 1]);
    var uniforms = frame.state.uniformData;
    uniforms.set([
        selectParam("shape", 3, 0), clampedNumber("ringCount", 20, 1, 30), clampedNumber("falloff", 1, 0, 5), booleanParam("inverseFalloff", false),
        color[0], color[1], color[2], color[3],
        zoom, cosine, sine, clampedNumber("offset", 0, 0, 1),
        aspect, transform.x, transform.y, 0,
    ]);
    device.queue.writeBuffer(frame.state.uniformBuf, 0, uniforms);
    var outputFormat = frame.output.format;
    if (frame.state.pipeline == null || frame.state.pipelineFormat !== outputFormat) {
        frame.state.pipeline = device.createRenderPipeline({
            layout: "auto",
            vertex: { module: frame.state.module, entryPoint: "vs_main", buffers: [{ arrayStride: 16, attributes: [{ shaderLocation: 0, format: "float32x2", offset: 0 }, { shaderLocation: 1, format: "float32x2", offset: 8 }] }] },
            fragment: { module: frame.state.module, entryPoint: "fs_main", targets: [{ format: outputFormat }] },
            primitive: { topology: "triangle-list" },
        });
        frame.state.pipelineFormat = outputFormat;
        frame.state.bindGroup = device.createBindGroup({ layout: frame.state.pipeline.getBindGroupLayout(0), entries: [{ binding: 0, resource: { buffer: frame.state.uniformBuf } }] });
    }
    var encoder = device.createCommandEncoder();
    var pass = encoder.beginRenderPass({ colorAttachments: [{ view: frame.output.createView(), loadOp: "clear", clearValue: { r: 0, g: 0, b: 0, a: 0 }, storeOp: "store" }] });
    pass.setPipeline(frame.state.pipeline);
    pass.setBindGroup(0, frame.state.bindGroup);
    pass.setVertexBuffer(0, frame.state.quad);
    pass.draw(6);
    pass.end();
    device.queue.submit([encoder.finish()]);
}
defineProperties(Effect, {
    shape: { type: "number", label: "Shape", defaultValue: 0, control: "select", options: [{ value: 0, label: "Triangle" }, { value: 1, label: "Circle" }, { value: 2, label: "Square" }] },
    ringCount: { type: "number", label: "Ring count", defaultValue: 20, control: "slider", min: 1, max: 30, step: 1 },
    falloff: { type: "number", label: "Falloff", defaultValue: 1, control: "slider", min: 0, max: 5, step: 0.1 },
    inverseFalloff: { type: "boolean", label: "Inverse falloff", defaultValue: false },
    color: { type: "color", label: "Color", defaultValue: { r: 1, g: 1, b: 1, a: 1 } },
    offset: { type: "number", label: "Offset", defaultValue: 0, control: "slider", min: 0, max: 1, step: 0.01 },
    transform: { type: "point-angle-radius", label: "Transform", defaultValue: { x: 50, y: 50, radius: 50, angle: 0 }, control: "point-angle-radius", mode: "canvas_and_ui", positionUnit: "%", radiusUnit: "%", minRadius: 0.1, maxRadius: 250 },
});

export const manifest = {
  "name": "Concentric patterns",
  "version": 2,
  "isAnimated": false,
  "usesMouse": false
}
