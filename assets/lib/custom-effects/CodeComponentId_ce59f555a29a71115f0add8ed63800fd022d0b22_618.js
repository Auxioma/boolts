
// Property metadata is only used in Figma Design, so this is a no-op.
function defineProperties(
  _component,
  _properties,
) {}

export default function Effect() { }
function finiteNumber(value, fallback) {
    const num = Number(value);
    return Number.isFinite(num) ? num : fallback;
}
function numberParam(params, name, fallback) {
    return finiteNumber(params[name], fallback);
}
function boolParam(params, name, fallback) {
    if (typeof params[name] === "boolean")
        return params[name] ? 1 : 0;
    return fallback ? 1 : 0;
}
function selectParam(params, name, values, fallbackIndex) {
    const raw = params[name];
    const value = raw != null && typeof raw === "object" ? raw.characters : raw;
    const index = values.indexOf(value);
    return index >= 0 ? index : fallbackIndex;
}
function writeColorParam(target, offset, params, name, fallback) {
    const value = params[name] || {};
    target[offset] = finiteNumber(value.r, fallback[0]);
    target[offset + 1] = finiteNumber(value.g, fallback[1]);
    target[offset + 2] = finiteNumber(value.b, fallback[2]);
    target[offset + 3] = finiteNumber(value.a, fallback[3]);
}
function writePointParam(target, offset, params, name, fallback) {
    const value = params[name] || {};
    target[offset] = finiteNumber(value.x, fallback[0]);
    target[offset + 1] = finiteNumber(value.y, fallback[1]);
}
function writePointAngleRadiusParam(target, offset, params, name, fallback) {
    const value = params[name] || {};
    target[offset] = finiteNumber(value.x, fallback[0]);
    target[offset + 1] = finiteNumber(value.y, fallback[1]);
    target[offset + 2] = finiteNumber(value.radius, fallback[2]);
    target[offset + 3] = finiteNumber(value.angle, fallback[3]);
}
function writeUniformIfChanged(device, buffer, data, lastData) {
    for (let i = 0; i < data.length; i++) {
        if (data[i] !== lastData[i]) {
            device.queue.writeBuffer(buffer, 0, data);
            lastData.set(data);
            return;
        }
    }
}
export function setup(device, frame) {
    frame.state.shapeValues = ["diamond", "circle", "box", "triangle", "hexagon", "cross", "ring"];
    frame.state.gridLayoutValues = ["grid", "brick", "hex"];
    frame.state.defaultTransform = [50, 50, 100, 0];
    frame.state.defaultShapeColor = [1, 1, 1, 1];
    frame.state.defaultBgColor = [0.102, 0.102, 0.18, 1];
    frame.state.defaultStrokeColor = [0.533, 0.533, 0.533, 1];
    frame.state.defaultOffset = [0, 0];
    const patternGridWGSL = `
diagnostic(off,derivative_uniformity);
struct Uniforms {
  dimensions: vec4f,
  shape: vec4f,
  gridLayout: vec4f,
  size: vec4f,
  transform: vec4f,
  strokeWidth: vec4f,
  shapeColor: vec4f,
  bgColor: vec4f,
  strokeColor: vec4f,
  zoom: vec4f,
  offset: vec4f,
};
@group(0) @binding(0) var<uniform> u: Uniforms;

struct VsIn {
  @location(0) pos: vec2f,
  @location(1) uv: vec2f,
};
struct VsOut {
  @builtin(position) position: vec4f,
  @location(0) uv: vec2f,
};

fn rot2(a: f32) -> mat2x2f {

  let c = cos(a);

  let s = sin(a);

  return mat2x2f(c, s, -s, c);

}

fn pmod(x: f32, m: f32) -> f32 {
  return ((x % m) + m) % m;
}

fn pmod2(x: vec2f, m: f32) -> vec2f {
  return ((x % m) + m) % m;
}

fn sdCircle(p: vec2f, r: f32) -> f32 {

  return length(p) - r;

}

fn sdBox(p: vec2f, r: f32) -> f32 {

  let d = abs(p) - vec2f(r);

  return length(max(d, vec2f(0.0))) + min(max(d.x, d.y), 0.0);

}

fn sdDiamond(p: vec2f, r: f32) -> f32 {

  let q = abs(p);

  return (q.x + q.y) - r;

}

fn sdTriangle(p_in: vec2f, r: f32) -> f32 {

  let k = sqrt(3.0);

  var p = p_in;

  p.x = abs(p.x) - r;

  p.y = p.y + r / k;

  if (p.x + k * p.y > 0.0) {

    p = vec2f(p.x - k * p.y, -k * p.x - p.y) / 2.0;

  }

  p.x = p.x - clamp(p.x, -2.0 * r, 0.0);

  return -length(p) * sign(p.y);

}

fn sdHexagon(p_in: vec2f, r: f32) -> f32 {

  let k = vec3f(-0.866025, 0.5, 0.577350);

  var p = abs(p_in);

  p = p - 2.0 * min(dot(k.xy, p), 0.0) * k.xy;

  p = p - vec2f(clamp(p.x, -k.z * r, k.z * r), r);

  return length(p) * sign(p.y);

}

fn sdCross(p: vec2f, r: f32) -> f32 {

  let arm = r * 0.35;

  let absP = abs(p);

  let a = length(max(absP - vec2f(arm, r), vec2f(0.0))) + min(max(absP.x - arm, absP.y - r), 0.0);

  let b = length(max(absP - vec2f(r, arm), vec2f(0.0))) + min(max(absP.x - r, absP.y - arm), 0.0);

  return min(a, b);

}

fn sdRing(p: vec2f, r: f32) -> f32 {

  return abs(length(p) - r * 0.75) - r * 0.25;

}

fn shapeSDF(p: vec2f, sh: i32, r: f32) -> f32 {

  if (sh == 0) { return sdDiamond(p, r * 1.4); }

  if (sh == 1) { return sdCircle(p, r); }

  if (sh == 2) { return sdBox(p, r); }

  if (sh == 3) { return sdTriangle(p, r); }

  if (sh == 4) { return sdHexagon(p, r); }

  if (sh == 5) { return sdCross(p, r); }

  if (sh == 6) { return sdRing(p, r); }

  return sdDiamond(p, r * 1.4);

}

@vertex fn vs_main(in: VsIn) -> VsOut {
  var out: VsOut;
  out.position = vec4f(in.pos, 0.0, 1.0);
  out.uv = in.uv;
  return out;
}

@fragment fn fs_main(@location(0) outputUv: vec2f) -> @location(0) vec4f {
  let dims = max(u.dimensions.xy, vec2f(1.0));
  let fragPx = outputUv * dims;
  let uvRaw = fragPx / dims;
  // shape: 0=diamond, 1=circle, 2=box, 3=triangle, 4=hexagon, 5=cross, 6=ring
  let shape = i32(u.shape.x + 0.5);
  // gridLayout: 0=grid, 1=brick, 2=hex
  let gridLayout = i32(u.gridLayout.x + 0.5);
  let spacing = u.transform.z;
  let txOffsetX = u.transform.x;
  let txOffsetY = u.transform.y;
  let rotation = u.transform.w;
  let size = u.size.x;
  let r = size * 0.5;
  let strokeWidth = u.strokeWidth.x;
  let shapeColor = u.shapeColor;
  let bgColor = u.bgColor;
  let strokeColor = u.strokeColor;
  let zoom = u.zoom.x;
  let uv = (uvRaw - vec2f(0.5)) / zoom + vec2f(0.5);
  // Seamless scroll offset: shift pixel coords then wrap with pmod so the
  // pattern tiles with no seam. offset is in percent (0..100 = one full tile).
  let scrollOffset = u.offset.xy * 0.01;
  // Convert uv to pixel coordinates
  var px = uv * dims;
  px = px - vec2f(txOffsetX, txOffsetY) * dims * 0.01;
  // Apply scroll offset in pixel-space, wrapped per-cell via pmod below.
  // We shift px by offset * spacing so 0→1 in offset = one full tile scroll.
  var scrollDelta: vec2f;
  if (gridLayout == 1) { // brick: Y period = 2 * spacing
    scrollDelta = vec2f(scrollOffset.x * spacing, scrollOffset.y * spacing * 2.0);
  } else if (gridLayout == 2) { // hex: Y period = 2 * spacing * 0.866
    scrollDelta = vec2f(scrollOffset.x * spacing, scrollOffset.y * spacing * 0.866 * 2.0);
  } else { // grid: Y period = spacing
    scrollDelta = scrollOffset * spacing;
  }
  px = px - scrollDelta;
  var cell = px;
  let hSpacing = spacing * 0.5;
  var baseRow = 0.0;
  if (gridLayout == 1) {
    baseRow = floor(px.y / spacing);
    cell.x = cell.x - pmod(baseRow, 2.0) * hSpacing;
  } else if (gridLayout == 2) {
    baseRow = floor(px.y / (spacing * 0.866));
    cell.x = cell.x - pmod(baseRow, 2.0) * hSpacing;
  }
  var cellUV: vec2f;
  if (gridLayout == 2) {
    cellUV.x = pmod(cell.x, spacing) - spacing * 0.5;
    cellUV.y = pmod(cell.y, spacing * 0.866) - spacing * 0.866 * 0.5;
  } else {
    cellUV = pmod2(cell, spacing) - spacing * 0.5;
  }
  // For shapes with tips/corners that may extend into neighboring cells (triangle, diamond),
  // evaluate the SDF from all 9 neighbor offsets and take the minimum distance.
  // This ensures tips crossing cell boundaries are rendered correctly.
  let rotMat = rot2(-rotation * 3.14159265 / 180.0);
  let cellUvBeforeRotation = cellUV;
  cellUV = rotMat * cellUV;

  var d = shapeSDF(cellUV, shape, r);
  // Only do neighbor sampling for shapes that have pointy tips extending beyond cell extents.
  // Triangle (sh==3) and Diamond (sh==0) are most affected; check all shapes for correctness.
  let clipCell = u.zoom.z > 0.5;
  let needsNeighbor = !clipCell || (shape == 3 || shape == 0);
  if (needsNeighbor) {
    for (var ny = -1; ny <= 1; ny++) {
      for (var nx = -1; nx <= 1; nx++) {
        if (nx == 0 && ny == 0) { continue; }
        var neighborOffset: vec2f;
        if (gridLayout == 1) { // brick
          let brickXAdj = (pmod(baseRow + f32(ny), 2.0) - pmod(baseRow, 2.0)) * hSpacing;
          neighborOffset = vec2f(f32(nx) * spacing + brickXAdj, f32(ny) * spacing);
        } else if (gridLayout == 2) { // hex
          let hexXAdj = (pmod(baseRow + f32(ny), 2.0) - pmod(baseRow, 2.0)) * hSpacing;
          neighborOffset = vec2f(f32(nx) * spacing + hexXAdj, f32(ny) * spacing * 0.866);
        } else { // regular grid
          neighborOffset = vec2f(f32(nx) * spacing, f32(ny) * spacing);
        }
        let ncUV = rotMat * (cellUvBeforeRotation - neighborOffset);
        let nd = shapeSDF(ncUV, shape, r);
        d = min(d, nd);
      }
    }
  }
  let aa = clamp(fwidth(d), 0.0, 1.5);
  let soft = max(aa, u.zoom.y);
  let fillAmt = 1.0 - smoothstep(-soft, soft, d);
  var strokeAmt = 0.0;
  if (strokeWidth > 0.0) {
    let sw = strokeWidth * 0.5;
    strokeAmt = 1.0 - smoothstep(-soft, soft, abs(d) - sw);
  }
  // Start with alpha-weighted background RGB.
  var outRgb = bgColor.rgb * bgColor.a;
  var outA = bgColor.a;
  // Blend shape color over background.
  let shapeBlend = fillAmt * shapeColor.a;
  outRgb = outRgb * (1.0 - shapeBlend) + shapeColor.rgb * shapeBlend;
  outA = outA * (1.0 - shapeBlend) + shapeBlend;
  // Blend stroke color over current result.
  let strokeBlend = strokeAmt * strokeColor.a;
  outRgb = outRgb * (1.0 - strokeBlend) + strokeColor.rgb * strokeBlend;
  outA = outA * (1.0 - strokeBlend) + strokeBlend;
  // Preserve the existing final alpha weighting.
  return vec4f(outRgb * outA, outA);
}
`;
    frame.state.module = device.createShaderModule({ code: patternGridWGSL });
    frame.state.pipeline = null;
    frame.state.pipelineFormat = null;
    frame.state.bindGroup = null;
    frame.state.outputTexture = null;
    frame.state.outputView = null;
    frame.state.vertexBuffers = [{
            arrayStride: 16,
            attributes: [
                { shaderLocation: 0, format: "float32x2", offset: 0 },
                { shaderLocation: 1, format: "float32x2", offset: 8 },
            ],
        }];
    frame.state.quad = device.createBuffer({
        size: 6 * 4 * 4,
        usage: GPUBufferUsage.VERTEX,
        mappedAtCreation: true,
    });
    new Float32Array(frame.state.quad.getMappedRange()).set([
        -1, -1, 0, 1,
        1, -1, 1, 1,
        -1, 1, 0, 0,
        -1, 1, 0, 0,
        1, -1, 1, 1,
        1, 1, 1, 0,
    ]);
    frame.state.quad.unmap();
    frame.state.uniformBuf = device.createBuffer({
        size: 176,
        usage: GPUBufferUsage.UNIFORM | GPUBufferUsage.COPY_DST,
    });
    frame.state.uniformData = new Float32Array(44);
    frame.state.lastUniformData = new Float32Array(44);
    frame.state.lastUniformData.fill(NaN);
}
export function render(device, frame) {
    const params = frame.params || {};
    const width = Math.max(1, finiteNumber(frame.output.width, 1));
    const height = Math.max(1, finiteNumber(frame.output.height, 1));
    const uniformData = frame.state.uniformData;
    uniformData[0] = width;
    uniformData[1] = height;
    uniformData[4] = selectParam(params, "shape", frame.state.shapeValues, 0);
    uniformData[8] = selectParam(params, "gridLayout", frame.state.gridLayoutValues, 0);
    uniformData[12] = numberParam(params, "size", 32);
    writePointAngleRadiusParam(uniformData, 16, params, "transform", frame.state.defaultTransform);
    uniformData[20] = numberParam(params, "strokeWidth", 0);
    writeColorParam(uniformData, 24, params, "shapeColor", frame.state.defaultShapeColor);
    writeColorParam(uniformData, 28, params, "bgColor", frame.state.defaultBgColor);
    writeColorParam(uniformData, 32, params, "strokeColor", frame.state.defaultStrokeColor);
    uniformData[36] = numberParam(params, "zoom", 1);
    uniformData[37] = numberParam(params, "softness", 0);
    // Preserve the existing fallback behavior when clipToCell is absent.
    uniformData[38] = boolParam(params, "clipToCell", true);
    writePointParam(uniformData, 40, params, "offset", frame.state.defaultOffset);
    writeUniformIfChanged(device, frame.state.uniformBuf, uniformData, frame.state.lastUniformData);
    const outputFormat = frame.output.format;
    if (frame.state.pipeline == null || frame.state.pipelineFormat !== outputFormat) {
        frame.state.pipeline = device.createRenderPipeline({
            layout: "auto",
            vertex: {
                module: frame.state.module,
                entryPoint: "vs_main",
                buffers: frame.state.vertexBuffers,
            },
            fragment: {
                module: frame.state.module,
                entryPoint: "fs_main",
                targets: [{ format: outputFormat }],
            },
            primitive: { topology: "triangle-list" },
        });
        frame.state.pipelineFormat = outputFormat;
        frame.state.bindGroup = device.createBindGroup({
            layout: frame.state.pipeline.getBindGroupLayout(0),
            entries: [
                { binding: 0, resource: { buffer: frame.state.uniformBuf } },
            ],
        });
    }
    if (frame.state.outputTexture !== frame.output) {
        frame.state.outputTexture = frame.output;
        frame.state.outputView = frame.output.createView();
    }
    const encoder = device.createCommandEncoder();
    const pass = encoder.beginRenderPass({
        colorAttachments: [{
                view: frame.state.outputView,
                loadOp: "clear",
                clearValue: { r: 0, g: 0, b: 0, a: 0 },
                storeOp: "store",
            }],
    });
    pass.setPipeline(frame.state.pipeline);
    pass.setBindGroup(0, frame.state.bindGroup);
    pass.setVertexBuffer(0, frame.state.quad);
    pass.draw(6);
    pass.end();
    device.queue.submit([encoder.finish()]);
}
defineProperties(Effect, {
    "shape": {
        type: "string",
        label: "Shape",
        defaultValue: "diamond",
        control: "select",
        options: [{ "value": "diamond", "label": "Diamond" }, { "value": "circle", "label": "Circle" }, { "value": "box", "label": "Box" }, { "value": "triangle", "label": "Triangle" }, { "value": "hexagon", "label": "Hexagon" }, { "value": "cross", "label": "Cross" }, { "value": "ring", "label": "Ring" }],
    },
    "gridLayout": {
        type: "string",
        label: "Layout",
        defaultValue: "grid",
        control: "select",
        options: [{ "value": "grid", "label": "Grid" }, { "value": "brick", "label": "Brick" }, { "value": "hex", "label": "Hex" }],
    },
    "size": {
        type: "number",
        label: "Size",
        defaultValue: 32,
        control: "slider",
        min: 4,
        max: 200,
        step: 1,
    },
    "zoom": {
        type: "number",
        label: "Scale",
        defaultValue: 1,
        control: "slider",
        min: 0.1,
        max: 5,
        step: 0.01,
    },
    "shapeColor": {
        type: "color",
        label: "Shape color",
        defaultValue: { "r": 1, "g": 1, "b": 1, "a": 1 },
    },
    "bgColor": {
        type: "color",
        label: "Background color",
        defaultValue: { "r": 0.102, "g": 0.102, "b": 0.18, "a": 1 },
    },
    "strokeColor": {
        type: "color",
        label: "Stroke color",
        defaultValue: { "r": 0.533, "g": 0.533, "b": 0.533, "a": 1 },
    },
    "strokeWidth": {
        type: "number",
        label: "Stroke width",
        defaultValue: 0,
        control: "slider",
        min: 0,
        max: 20,
        step: 0.5,
    },
    "softness": {
        type: "number",
        label: "Softness",
        defaultValue: 0,
        control: "slider",
        min: 0,
        max: 50,
        step: 0.5,
    },
    "clipToCell": {
        type: "boolean",
        label: "Clip to cell",
        defaultValue: false,
    },
    "offset": {
        type: "point",
        label: "Offset",
        defaultValue: { x: 0, y: 0 },
        control: "point",
        mode: "ui",
        unit: "%",
    },
    "transform": {
        type: "point-angle-radius",
        label: "Transform",
        defaultValue: { x: 50, y: 50, angle: 0, radius: 100 },
        positionUnit: "%",
        radiusUnit: "px",
    },
});

export const manifest = {
  "name": "Pattern grid",
  "version": 2,
  "isAnimated": false,
  "usesMouse": false
}
