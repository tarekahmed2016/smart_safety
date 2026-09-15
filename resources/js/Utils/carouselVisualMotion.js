/**
 * Horizontal carousel motion in screen space.
 *
 * Positive offset is applied as translateX(-offset), so a larger offset
 * moves the track (and cards) to the left. These helpers ignore document
 * dir/RTL so a `direction: ltr` track does not get inverted twice.
 */

export function trackTranslateX(offset) {
  return -offset
}

export function pointerOffsetDelta(startX, currentX) {
  return startX - currentX
}

export function applyPointerMove(startOffset, startX, currentX) {
  const offset = startOffset + pointerOffsetDelta(startX, currentX)

  return {
    offset,
    translateX: trackTranslateX(offset),
  }
}

export function applyArrowMove(startOffset, direction, stepSize) {
  const offset = startOffset + direction * stepSize

  return {
    offset,
    translateX: trackTranslateX(offset),
  }
}

export function describePointerMotion(startX, currentX) {
  const { translateX } = applyPointerMove(0, startX, currentX)

  if (currentX < startX) {
    return {
      pointer: 'left',
      element: translateX < 0 ? 'left' : 'right',
      translateX,
    }
  }

  if (currentX > startX) {
    return {
      pointer: 'right',
      element: translateX > 0 ? 'right' : 'left',
      translateX,
    }
  }

  return {
    pointer: 'none',
    element: 'none',
    translateX,
  }
}
