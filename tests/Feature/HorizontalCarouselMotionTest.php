<?php

use Illuminate\Support\Facades\Process;

test('carousel pointer and arrows move elements in the same visual direction for rtl and ltr', function () {
    $script = <<<'JS'
import {
  applyArrowMove,
  applyPointerMove,
  describePointerMotion,
} from './resources/js/Utils/carouselVisualMotion.js';

const directions = ['ltr', 'rtl'];

const pointer = Object.fromEntries(directions.map((dir) => {
  const dragLeft = describePointerMotion(200, 140);
  const dragRight = describePointerMotion(200, 260);

  return [dir, {
    dragLeft,
    dragRight,
    dragLeftMove: applyPointerMove(50, 200, 140),
    dragRightMove: applyPointerMove(50, 200, 260),
  }];
}));

const arrows = Object.fromEntries(directions.map((dir) => {
  return [dir, {
    left: applyArrowMove(80, -1, 40),
    right: applyArrowMove(80, 1, 40),
  }];
}));

console.log(JSON.stringify({ pointer, arrows }));
JS;

    $result = json_decode(
        Process::path(base_path())->run(['node', '--input-type=module', '-e', $script])->throw()->output(),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    foreach (['ltr', 'rtl'] as $dir) {
        expect($result['pointer'][$dir]['dragLeft']['pointer'])->toBe('left')
            ->and($result['pointer'][$dir]['dragLeft']['element'])->toBe('left')
            ->and($result['pointer'][$dir]['dragLeft']['translateX'])->toBe(-60)
            ->and($result['pointer'][$dir]['dragRight']['pointer'])->toBe('right')
            ->and($result['pointer'][$dir]['dragRight']['element'])->toBe('right')
            ->and($result['pointer'][$dir]['dragRight']['translateX'])->toBe(60)
            ->and($result['pointer'][$dir]['dragLeftMove']['offset'])->toBe(110)
            ->and($result['pointer'][$dir]['dragLeftMove']['translateX'])->toBe(-110)
            ->and($result['pointer'][$dir]['dragRightMove']['offset'])->toBe(-10)
            ->and($result['pointer'][$dir]['dragRightMove']['translateX'])->toBe(10);
    }

    expect($result['pointer']['rtl'])->toEqual($result['pointer']['ltr'])
        ->and($result['arrows']['rtl'])->toEqual($result['arrows']['ltr'])
        ->and($result['arrows']['ltr']['left']['offset'])->toBe(40)
        ->and($result['arrows']['ltr']['left']['translateX'])->toBe(-40)
        ->and($result['arrows']['ltr']['right']['offset'])->toBe(120)
        ->and($result['arrows']['ltr']['right']['translateX'])->toBe(-120);
});

test('products and clients carousels follow visual motion without rtl inversion', function () {
    $products = file_get_contents(resource_path('js/Components/Public/ProductsCarousel.vue'));
    $clients = file_get_contents(resource_path('js/Components/Public/ClientsPartnersCarousel.vue'));
    $team = file_get_contents(resource_path('js/Components/Public/TeamMembersCarousel.vue'));
    $composable = file_get_contents(resource_path('js/Composables/useHorizontalCarousel.js'));
    $styles = file_get_contents(resource_path('css/plastex.css'));

    expect($products)->toContain('followVisualMotion: true')
        ->and($products)->toContain('@keydown="onKeydown"')
        ->and($products)->toContain(':dir="locale === \'ar\' ? \'rtl\' : \'ltr\'"')
        ->and($clients)->toContain('followVisualMotion: true')
        ->and($clients)->toContain('@keydown="onKeydown"')
        ->and($team)->not->toContain('followVisualMotion')
        ->and($composable)->toContain('followVisualMotion')
        ->and($composable)->toContain('applyPointerMove(dragStartOffset, dragStartX, event.clientX)')
        ->and($composable)->toContain('applyArrowMove(offset.value, direction, stepSize)')
        ->and($styles)->toContain('direction: ltr;');

    expect($styles)->not->toContain('[dir="rtl"] .px-product-carousel')
        ->and($styles)->not->toContain('[dir="rtl"] .px-horizontal-carousel-track')
        ->and($styles)->not->toContain('flex-direction: row-reverse');
});
