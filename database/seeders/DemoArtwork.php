<?php

namespace Database\Seeders;

use GdImage;
use InvalidArgumentException;

/**
 * Cover images for the demonstration content: interface pieces (windows,
 * phones, charts, cards) on soft brand backgrounds, in the style of the
 * site's own illustrations. They contain no text, logos or photographs.
 *
 * Pictures are drawn at twice their size and scaled down, which smooths
 * the edges of the shapes; backgrounds and shadows are drawn small and
 * scaled up, which softens them.
 */
final class DemoArtwork
{
    private const WIDTH = 1600;

    private const HEIGHT = 1000;

    /** Drawing scale of the shapes. */
    private const SCALE = 2;

    /** Size of a background pixel, in picture pixels. */
    private const BACKDROP = 8;

    /** Every picture this class can draw. */
    public const KINDS = [
        'web', 'mobile', 'erp', 'ai', 'shield', 'sparkle',
        'launch', 'products', 'compare', 'modules', 'network', 'cloud', 'access', 'design',
    ];

    private const DARK = ['shield', 'sparkle', 'products'];

    /** @var array<string, array{int, int, int}> */
    private const COLORS = [
        'white' => [255, 255, 255],
        'ink' => [74, 60, 85],
        'primary' => [72, 20, 103],
        'primarySoft' => [91, 42, 128],
        'secondary' => [109, 40, 160],
        'violet' => [123, 53, 184],
        'violetDeep' => [76, 26, 120],
        'night' => [22, 12, 32],
        'nightSoft' => [36, 22, 49],
        'nightLine' => [74, 56, 92],
        'nightText' => [205, 191, 224],
        'glass' => [52, 34, 74],
        'glassLine' => [92, 70, 116],
        'lavender' => [245, 240, 250],
        'lavenderStrong' => [235, 226, 244],
        'purple300' => [217, 201, 232],
        'purple400' => [185, 155, 214],
        'orchid' => [200, 107, 184],
        'blush' => [251, 240, 247],
        'band' => [246, 227, 241],
        'accent' => [244, 183, 64],
        'success' => [21, 128, 61],
        'successSoft' => [220, 245, 230],
        'canvas' => [250, 248, 252],
        'border' => [232, 225, 237],
        'paleText' => [233, 223, 242],
        'mutedText' => [199, 176, 220],
    ];

    private GdImage $canvas;

    private GdImage $backdrop;

    /** Where the shadows fall, white on black, at the backdrop's size. */
    private GdImage $shadows;

    /** Whether the drawing pass only collects the shadows. */
    private bool $collecting = false;

    /**
     * The picture of the given kind, as JPEG bytes.
     */
    public function jpeg(string $kind): string
    {
        if (! in_array($kind, self::KINDS, true)) {
            throw new InvalidArgumentException("Unknown artwork [{$kind}].");
        }

        $dark = in_array($kind, self::DARK, true);
        $this->backdrop = $this->backdrop($dark);
        $this->shadows = $this->image(imagesx($this->backdrop), imagesy($this->backdrop));
        $draw = match ($kind) {
            'web' => $this->web(...),
            'mobile' => $this->mobile(...),
            'erp' => $this->erp(...),
            'ai' => $this->ai(...),
            'shield' => fn () => $this->product('shield'),
            'sparkle' => fn () => $this->product('sparkle'),
            'launch' => $this->launch(...),
            'products' => $this->products(...),
            'compare' => $this->compare(...),
            'modules' => $this->modules(...),
            'network' => $this->network(...),
            'cloud' => $this->cloud(...),
            'access' => $this->access(...),
            'design' => $this->design(...),
        };

        // First pass: the shadows, blurred onto the backdrop; second pass: the shapes.
        $this->collecting = true;
        $draw();
        $this->collecting = false;
        $this->castShadows($dark ? 0.5 : 0.2);

        $this->canvas = $this->scaled($this->backdrop, self::WIDTH * self::SCALE, self::HEIGHT * self::SCALE);
        $draw();

        $picture = $this->scaled($this->canvas, self::WIDTH, self::HEIGHT);

        ob_start();
        imagejpeg($picture, null, 86);

        return (string) ob_get_clean();
    }

    // ------------------------------------------------------------------ Pictures

    private function web(): void
    {
        $this->panel(230, 150, 1140, 900, 30);
        $this->windowBar(230, 150, 1140);

        // Calendar: the booked days are filled.
        $this->bar(300, 270, 220, 20, 'ink');
        $this->bar(300, 304, 140, 14, 'purple300');
        $this->circle(790, 284, 18, 'lavender');
        $this->circle(740, 284, 18, 'lavender');
        $booked = [3 => 'secondary', 9 => 'secondary', 12 => 'primary', 16 => 'purple300', 22 => 'secondary', 27 => 'purple300', 31 => 'secondary'];

        for ($cell = 0; $cell < 35; $cell++) {
            $x = 300 + ($cell % 7) * 72;
            $y = 350 + intdiv($cell, 7) * 66;
            $this->rounded($x, $y, 60, 54, 14, $booked[$cell] ?? 'lavender');
        }

        foreach (['secondary', 'orchid', 'accent'] as $index => $color) {
            $x = 300 + $index * 168;
            $this->rounded($x, 700, 150, 120, 20, 'lavender');
            $this->circle($x + 38, 740, 16, $color);
            $this->bar($x + 22, 774, 96, 14, 'purple300');
        }

        // Appointments.
        foreach (['purple300', 'orchid', 'accent', 'lavenderStrong', 'purple300'] as $index => $avatar) {
            $y = 250 + $index * 120;
            $this->rounded(880, $y, 450, 100, 20, 'border');
            $this->rounded(882, $y + 2, 446, 96, 18, 'canvas');
            $this->circle(934, $y + 50, 28, $avatar);
            $this->bar(980, $y + 30, 190, 16, 'ink');
            $this->bar(980, $y + 58, 120, 12, 'purple300');
            $this->bar(1200, $y + 34, 100, 32, $index === 0 ? 'successSoft' : 'lavender');
        }

        $this->panel(1050, 70, 440, 130, 26, shadow: true);
        $this->circle(1116, 135, 34, 'successSoft');
        $this->check(1098, 136, 1112, 150, 1136, 122, 'success', 7);
        $this->bar(1170, 110, 230, 18, 'ink');
        $this->bar(1170, 144, 160, 14, 'purple300');
    }

    private function mobile(): void
    {
        // A list behind, on the second phone.
        $this->rounded(1040, 230, 340, 720, 54, 'nightSoft', shadow: true);
        $this->rounded(1054, 244, 312, 692, 42, 'white');

        foreach (range(0, 4) as $row) {
            $y = 320 + $row * 112;
            $this->circle(1106, $y, 26, $row === 0 ? 'secondary' : 'lavenderStrong');
            $this->bar(1146, $y - 18, 160, 14, 'ink');
            $this->bar(1146, $y + 6, 110, 12, 'purple300');
        }

        // The tracking phone: map, route, driver, bottom sheet.
        $this->rounded(560, 110, 420, 880, 62, 'night', shadow: true);
        $this->rounded(576, 126, 388, 848, 48, 'lavender');
        $this->line([[576, 330], [964, 250]], 'white', 22);
        $this->line([[700, 126], [760, 700]], 'white', 22);
        $this->line([[576, 560], [964, 520]], 'white', 22);
        $this->line([[620, 640], [700, 530], [830, 480], [900, 300]], 'secondary', 12);
        $this->circle(620, 640, 18, 'primary');
        $this->circle(620, 640, 7, 'white');
        $this->circle(900, 300, 24, 'orchid');
        $this->circle(900, 300, 9, 'white');
        $this->circle(770, 500, 20, 'white');
        $this->circle(770, 500, 13, 'accent');
        $this->rounded(720, 140, 100, 26, 13, 'night');

        $this->rounded(576, 660, 388, 314, 40, 'white');
        $this->bar(740, 682, 60, 8, 'lavenderStrong');
        $this->circle(634, 744, 30, 'purple300');
        $this->bar(680, 724, 170, 16, 'ink');
        $this->bar(680, 752, 110, 12, 'purple300');
        $this->bar(860, 728, 80, 32, 'successSoft');
        $this->bar(610, 812, 320, 14, 'lavender');
        $this->bar(610, 812, 210, 14, 'secondary');
        $this->bar(610, 864, 320, 62, 'primary');

        // A notification.
        $this->panel(170, 210, 390, 116, 28, shadow: true);
        $this->circle(236, 268, 34, 'secondary');
        $this->circle(236, 268, 11, 'white');
        $this->bar(290, 244, 220, 16, 'ink');
        $this->bar(290, 276, 150, 13, 'purple300');
    }

    private function erp(): void
    {
        $this->panel(170, 130, 1260, 900, 30);
        $this->rounded(170, 130, 250, 900, 30, 'night');
        $this->rounded(390, 130, 30, 900, 0, 'night');
        $this->rounded(206, 170, 52, 52, 14, 'orchid');
        $this->rounded(196, 312, 196, 50, 16, 'nightSoft');

        foreach (range(0, 5) as $item) {
            $y = 274 + $item * 62;
            $this->circle(224, $y + 13, 10, $item === 1 ? 'orchid' : 'nightLine');
            $this->bar(250, $y + 7, 112, 13, $item === 1 ? 'paleText' : 'nightLine');
        }

        $this->bar(470, 170, 270, 22, 'ink');
        $this->bar(470, 206, 170, 14, 'purple300');
        $this->bar(1080, 168, 240, 42, 'lavender');
        $this->circle(1366, 189, 24, 'purple300');

        foreach (['secondary', 'orchid', 'accent', 'primary'] as $index => $color) {
            $x = 470 + $index * 236;
            $this->rounded($x, 252, 214, 150, 22, 'lavender');
            $this->circle($x + 44, 296, 24, 'white');
            $this->circle($x + 44, 296, 9, $color);
            $this->bar($x + 24, 336, 120, 13, 'purple300');
            $this->bar($x + 24, 362, 150, 22, 'primarySoft');
        }

        // Monthly figures.
        $this->rounded(470, 430, 610, 430, 24, 'border');
        $this->rounded(472, 432, 606, 426, 22, 'white');
        $this->bar(504, 466, 170, 16, 'ink');

        foreach ([140, 210, 170, 260, 230, 300, 250, 340, 290] as $index => $height) {
            $color = $height === 340 ? 'secondary' : ($index % 3 === 0 ? 'purple400' : 'lavenderStrong');
            $this->rounded(510 + $index * 62, 820 - $height, 36, $height, 10, $color);
        }

        // Latest documents.
        $this->rounded(1100, 430, 300, 430, 24, 'border');
        $this->rounded(1102, 432, 296, 426, 22, 'white');
        $this->bar(1130, 466, 140, 16, 'ink');

        foreach (['success', 'accent', 'success', 'orchid', 'success'] as $row => $status) {
            $y = 530 + $row * 64;
            $this->circle(1150, $y, 20, 'lavenderStrong');
            $this->bar(1184, $y - 14, 130, 12, 'ink');
            $this->bar(1184, $y + 6, 90, 10, 'purple300');
            $this->circle(1364, $y, 8, $status);
        }
    }

    private function ai(): void
    {
        $this->panel(170, 170, 920, 780, 30);
        $this->bar(220, 220, 240, 22, 'ink');
        $this->bar(220, 256, 160, 14, 'purple300');
        $this->bar(820, 218, 76, 34, 'primary');
        $this->bar(906, 218, 76, 34, 'lavender');
        $this->bar(992, 218, 76, 34, 'lavender');

        foreach (range(0, 4) as $level) {
            $this->rounded(220, 340 + $level * 110, 820, 3, 0, 'lavenderStrong');
        }

        // History, then the forecast with its range.
        $history = [[220, 720], [320, 680], [420, 710], [520, 610], [620, 640], [700, 560]];
        $forecast = [[700, 560], [800, 520], [900, 450], [1040, 400]];
        $this->polygon([[700, 560], [800, 470], [900, 380], [1040, 320], [1040, 480], [900, 520], [800, 570], [700, 560]], 'band');
        $this->polygon([...$history, [700, 800], [220, 800]], 'lavender');
        $this->line($history, 'secondary', 9);
        $this->dashed($forecast, 'orchid', 9);
        $this->circle(700, 560, 16, 'secondary');
        $this->circle(700, 560, 7, 'white');

        foreach (range(0, 6) as $tick) {
            $this->bar(232 + $tick * 132, 830, 60, 12, 'purple300');
        }

        // The assistant.
        $this->panel(1010, 110, 440, 340, 28, shadow: true);
        $this->circle(1064, 166, 28, 'secondary');
        $this->sparkle(1064, 166, 15, 'white');
        $this->bar(1106, 156, 150, 18, 'ink');
        $this->rounded(1040, 220, 320, 86, 22, 'lavender');
        $this->bar(1066, 244, 260, 13, 'purple300');
        $this->bar(1066, 270, 190, 13, 'purple300');
        $this->rounded(1110, 324, 310, 100, 22, 'primary');
        $this->bar(1136, 350, 250, 13, 'paleText');
        $this->bar(1136, 378, 180, 13, 'mutedText');

        $nodes = [[1190, 640, 26, 'secondary'], [1320, 580, 20, 'orchid'], [1410, 700, 24, 'accent'], [1280, 780, 18, 'purple400'], [1160, 840, 22, 'purple300']];
        $this->line([[1190, 640], [1320, 580], [1410, 700], [1280, 780], [1190, 640]], 'purple300', 5);
        $this->line([[1280, 780], [1160, 840]], 'purple300', 5);

        foreach ($nodes as [$x, $y, $radius, $color]) {
            $this->circle($x, $y, $radius, $color);
        }
    }

    private function product(string $mark): void
    {
        $this->ring(800, 450, 380, 170, 'nightLine', 3);
        $this->ring(800, 450, 520, 250, 'nightSoft', 3);
        $this->rounded(596, 246, 408, 408, 70, 'glassLine', shadow: true);
        $this->rounded(600, 250, 400, 400, 66, 'glass');

        if ($mark === 'shield') {
            $this->polygon([[800, 326], [906, 366], [896, 486], [800, 572], [704, 486], [694, 366]], 'orchid');
            $this->polygon([[800, 360], [876, 388], [868, 476], [800, 538], [732, 476], [724, 388]], 'violet');
            $this->check(750, 450, 788, 488, 856, 410, 'white', 14);
        } else {
            $this->sparkle(800, 456, 130, 'orchid');
            $this->sparkle(906, 340, 42, 'accent');
            $this->sparkle(700, 566, 28, 'white');
        }

        $this->rounded(270, 372, 270, 88, 44, 'glass', shadow: true);
        $this->circle(316, 416, 24, 'success');
        $this->bar(356, 400, 150, 13, 'nightText');
        $this->bar(356, 424, 100, 11, 'nightLine');

        $this->rounded(1060, 536, 280, 88, 44, 'glass', shadow: true);
        $this->circle(1106, 580, 24, 'accent');
        $this->bar(1146, 564, 160, 13, 'nightText');
        $this->bar(1146, 588, 110, 11, 'nightLine');

        foreach ([[420, 240, 7], [1210, 300, 6], [1150, 760, 8], [480, 720, 5]] as [$x, $y, $radius]) {
            $this->circle($x, $y, $radius, 'purple400');
        }
    }

    private function launch(): void
    {
        $this->panel(250, 140, 1100, 900, 30);
        $this->windowBar(250, 140, 1100);
        $this->bar(320, 290, 420, 34, 'ink');
        $this->bar(320, 342, 340, 34, 'ink');
        $this->bar(320, 410, 380, 14, 'purple300');
        $this->bar(320, 438, 300, 14, 'purple300');
        $this->bar(320, 496, 180, 56, 'primary');

        foreach (['primary', 'lavender', 'lavender'] as $index => $color) {
            $this->bar(320 + $index * 96, 600, 84, 40, $color);
        }

        $this->rounded(800, 270, 480, 380, 26, 'secondary');
        $this->circle(1040, 460, 120, 'violet');
        $this->ring(1040, 460, 120, 46, 'purple400', 4);
        $this->ring(1040, 460, 46, 120, 'purple400', 4);
        $this->ring(1040, 460, 120, 120, 'purple400', 4);

        foreach (range(0, 2) as $card) {
            $x = 320 + $card * 330;
            $this->rounded($x, 720, 300, 200, 22, 'lavender');
            $this->circle($x + 46, 770, 22, 'white');
            $this->bar($x + 26, 820, 200, 14, 'purple300');
            $this->bar($x + 26, 846, 150, 14, 'purple300');
        }
    }

    private function products(): void
    {
        $this->ring(800, 470, 640, 260, 'nightLine', 3);

        foreach (['shield' => 330, 'sparkle' => 870] as $mark => $x) {
            $this->rounded($x - 4, 266, 408, 408, 70, 'glassLine', shadow: true);
            $this->rounded($x, 270, 400, 400, 66, 'glass');
            $center = $x + 200;

            if ($mark === 'shield') {
                $this->polygon([[$center, 356], [$center + 96, 392], [$center + 88, 500], [$center, 578], [$center - 88, 500], [$center - 96, 392]], 'orchid');
                $this->check($center - 46, 470, $center - 10, 506, $center + 52, 434, 'white', 13);
            } else {
                $this->sparkle($center, 470, 118, 'orchid');
                $this->sparkle($center + 100, 360, 36, 'accent');
            }
        }
    }

    private function compare(): void
    {
        // A page that informs…
        $this->panel(150, 160, 640, 760, 28);
        $this->windowBar(150, 160, 640);
        $this->rounded(200, 270, 540, 220, 20, 'secondary');
        $this->bar(200, 530, 380, 24, 'ink');
        $this->bar(200, 572, 500, 13, 'purple300');
        $this->bar(200, 598, 440, 13, 'purple300');
        $this->bar(200, 624, 470, 13, 'purple300');

        foreach (range(0, 2) as $card) {
            $this->rounded(200 + $card * 186, 680, 166, 180, 18, 'lavender');
        }

        // …and an application that does work.
        $this->panel(810, 220, 640, 720, 28, shadow: true);
        $this->windowBar(810, 220, 640);
        $this->bar(860, 320, 260, 22, 'ink');

        foreach (range(0, 2) as $field) {
            $y = 380 + $field * 110;
            $this->bar(860, $y, 140, 12, 'purple300');
            $this->rounded(860, $y + 24, 540, 56, 16, 'border');
            $this->rounded(862, $y + 26, 536, 52, 14, 'canvas');
        }

        $this->bar(860, 730, 96, 44, 'secondary');
        $this->circle(934, 752, 18, 'white');
        $this->bar(976, 744, 200, 14, 'purple300');
        $this->bar(860, 820, 540, 64, 'primary');
    }

    private function modules(): void
    {
        $positions = [[300, 190], [670, 120], [1040, 190], [300, 630], [670, 700], [1040, 630]];
        $colors = ['secondary', 'orchid', 'accent', 'primary', 'purple400', 'success'];

        foreach ($positions as [$x, $y]) {
            $this->line([[$x + 130, $y + 90], [800, 500]], 'purple300', 6);
        }

        $this->circle(800, 500, 118, 'white', shadow: true);
        $this->circle(800, 500, 92, 'primary');
        $this->ring(800, 500, 60, 60, 'purple400', 6);
        $this->circle(800, 500, 20, 'white');

        foreach ($positions as $index => [$x, $y]) {
            $this->panel($x, $y, 260, 180, 26, shadow: true);
            $this->circle($x + 56, $y + 56, 28, 'lavender');
            $this->circle($x + 56, $y + 56, 11, $colors[$index]);
            $this->bar($x + 30, $y + 104, 170, 14, 'ink');
            $this->bar($x + 30, $y + 132, 120, 12, 'purple300');
        }
    }

    private function network(): void
    {
        $nodes = [[260, 300], [480, 200], [430, 520], [700, 380], [640, 720], [920, 260], [960, 600], [1180, 420], [1240, 760], [1380, 240]];
        $edges = [[0, 1], [0, 2], [1, 3], [2, 3], [2, 4], [3, 5], [3, 6], [4, 6], [5, 7], [6, 7], [6, 8], [7, 9], [5, 9], [7, 8]];

        foreach ($edges as [$from, $to]) {
            $this->line([$nodes[$from], $nodes[$to]], 'purple300', 6);
        }

        $colors = ['purple400', 'orchid', 'purple300', 'secondary', 'accent', 'purple400', 'primary', 'secondary', 'orchid', 'accent'];

        foreach ($nodes as $index => [$x, $y]) {
            $radius = $index === 3 || $index === 7 ? 46 : 28;
            $this->circle($x, $y, $radius + 10, 'white', shadow: $radius > 30);
            $this->circle($x, $y, $radius, $colors[$index]);
        }

        $this->sparkle(700, 380, 22, 'white');
        $this->sparkle(1180, 420, 22, 'white');
    }

    private function cloud(): void
    {
        $this->rounded(520, 360, 560, 130, 65, 'white', shadow: true);
        $this->circle(800, 300, 170, 'white');
        $this->circle(640, 360, 120, 'white');
        $this->circle(970, 380, 110, 'white');
        $this->bar(700, 380, 200, 18, 'purple300');
        $this->bar(740, 414, 120, 18, 'lavenderStrong');

        $this->dashed([[800, 500], [800, 590]], 'purple400', 6);
        $this->dashed([[520, 470], [380, 600]], 'purple400', 6);
        $this->dashed([[1080, 470], [1220, 600]], 'purple400', 6);

        foreach (range(0, 2) as $row) {
            $y = 600 + $row * 104;
            $this->panel(560, $y, 480, 84, 22, shadow: true);
            $this->circle(604, $y + 42, 11, $row === 1 ? 'accent' : 'success');
            $this->circle(640, $y + 42, 11, 'purple300');
            $this->bar(680, $y + 35, 220, 14, 'lavenderStrong');
            $this->bar(930, $y + 35, 70, 14, 'lavender');
        }

        $this->circle(340, 640, 64, 'white', shadow: true);
        $this->circle(340, 640, 40, 'secondary');
        $this->circle(1260, 640, 64, 'white', shadow: true);
        $this->circle(1260, 640, 40, 'primary');
        $this->ring(1260, 640, 40, 16, 'purple400', 4);
    }

    private function access(): void
    {
        $this->panel(260, 200, 520, 600, 34, shadow: true);
        $this->rounded(310, 250, 420, 240, 24, 'night');
        $this->bar(350, 300, 260, 30, 'white');
        $this->bar(350, 350, 200, 18, 'mutedText');
        $this->bar(350, 400, 140, 50, 'white');
        $this->rounded(310, 530, 420, 220, 24, 'lavender');
        $this->bar(350, 580, 260, 30, 'primary');
        $this->bar(350, 630, 200, 18, 'secondary');

        // A focused button, with its ring.
        $this->panel(860, 260, 520, 300, 34, shadow: true);
        $this->rounded(940, 360, 360, 104, 52, 'secondary');
        $this->rounded(950, 370, 340, 84, 42, 'white');
        $this->rounded(960, 380, 320, 64, 32, 'primary');
        $this->bar(1050, 404, 140, 16, 'paleText');

        foreach (range(0, 3) as $key) {
            $this->panel(860 + $key * 132, 620, 112, 112, 22, shadow: true);
            $this->rounded(890 + $key * 132, 668, 52, 14, 7, $key === 1 ? 'secondary' : 'purple300');
        }
    }

    private function design(): void
    {
        foreach (['primary', 'secondary', 'orchid', 'accent', 'purple300'] as $index => $color) {
            $this->circle(300 + $index * 130, 230, 56, 'white', shadow: true);
            $this->circle(300 + $index * 130, 230, 44, $color);
        }

        $this->panel(220, 360, 1160, 600, 30, shadow: true);
        $this->rounded(270, 410, 300, 500, 20, 'lavender');

        foreach (range(0, 4) as $row) {
            $this->bar(300, 450 + $row * 56, 200 - $row * 20, 14, 'purple300');
        }

        $this->rounded(610, 410, 720, 260, 20, 'lavenderStrong');
        $this->line([[640, 640], [1300, 440]], 'purple300', 4);
        $this->line([[640, 440], [1300, 640]], 'purple300', 4);
        $this->rounded(610, 700, 350, 210, 20, 'lavender');
        $this->rounded(980, 700, 350, 210, 20, 'lavender');
        $this->bar(640, 740, 220, 18, 'ink');
        $this->bar(1010, 740, 220, 18, 'ink');
    }

    // ------------------------------------------------------------------ Shapes

    /** A white panel (window or card) with a soft shadow. */
    private function panel(float $x, float $y, float $width, float $height, float $radius, bool $shadow = true): void
    {
        $this->rounded($x, $y, $width, $height, $radius, 'white', $shadow);
    }

    /** The dots and address bar of a browser window, and the line below them. */
    private function windowBar(float $x, float $y, float $width): void
    {
        foreach (['orchid', 'accent', 'purple300'] as $index => $color) {
            $this->circle($x + 40 + $index * 30, $y + 34, 9, $color);
        }

        $this->bar($x + 150, $y + 20, min(520, $width - 260), 28, 'lavender');
        $this->rounded($x, $y + 66, $width, 2, 0, 'border');
    }

    /** A rounded bar (a line of text, a button, a pill). */
    private function bar(float $x, float $y, float $width, float $height, string $color): void
    {
        $this->rounded($x, $y, $width, $height, $height / 2, $color);
    }

    private function rounded(float $x, float $y, float $width, float $height, float $radius, string $color, bool $shadow = false): void
    {
        if ($shadow) {
            $this->shadow($x, $y, $width, $height);
        }

        if ($this->collecting) {
            return;
        }

        $s = self::SCALE;
        $fill = $this->color($color);
        $radius = min($radius, $width / 2, $height / 2) * $s;
        [$left, $top, $right, $bottom] = [(int) round($x * $s), (int) round($y * $s), (int) round(($x + $width) * $s) - 1, (int) round(($y + $height) * $s) - 1];
        $r = (int) round($radius);

        if ($r < 1) {
            imagefilledrectangle($this->canvas, $left, $top, $right, $bottom, $fill);

            return;
        }

        imagefilledrectangle($this->canvas, $left + $r, $top, $right - $r, $bottom, $fill);
        imagefilledrectangle($this->canvas, $left, $top + $r, $right, $bottom - $r, $fill);

        foreach ([[$left + $r, $top + $r], [$right - $r, $top + $r], [$left + $r, $bottom - $r], [$right - $r, $bottom - $r]] as [$cx, $cy]) {
            imagefilledellipse($this->canvas, $cx, $cy, 2 * $r + 1, 2 * $r + 1, $fill);
        }
    }

    private function circle(float $x, float $y, float $radius, string $color, bool $shadow = false): void
    {
        if ($shadow) {
            $this->shadow($x - $radius, $y - $radius, 2 * $radius, 2 * $radius);
        }

        if ($this->collecting) {
            return;
        }

        $s = self::SCALE;
        $diameter = (int) round(2 * $radius * $s);
        imagefilledellipse($this->canvas, (int) round($x * $s), (int) round($y * $s), $diameter, $diameter, $this->color($color));
    }

    /**
     * @param  list<array{int|float, int|float}>  $points
     */
    private function polygon(array $points, string $color): void
    {
        if ($this->collecting) {
            return;
        }

        $flat = [];

        foreach ($points as [$x, $y]) {
            $flat[] = (int) round($x * self::SCALE);
            $flat[] = (int) round($y * self::SCALE);
        }

        imagefilledpolygon($this->canvas, $flat, $this->color($color));
    }

    /**
     * A thick line through the points, with round ends and joins.
     *
     * @param  list<array{int|float, int|float}>  $points
     */
    private function line(array $points, string $color, float $thickness): void
    {
        if ($this->collecting) {
            return;
        }

        $s = self::SCALE;
        $fill = $this->color($color);
        imagesetthickness($this->canvas, (int) round($thickness * $s));

        for ($index = 1; $index < count($points); $index++) {
            [$x1, $y1] = $points[$index - 1];
            [$x2, $y2] = $points[$index];
            imageline($this->canvas, (int) round($x1 * $s), (int) round($y1 * $s), (int) round($x2 * $s), (int) round($y2 * $s), $fill);
        }

        imagesetthickness($this->canvas, 1);

        foreach ($points as [$x, $y]) {
            $this->circle($x, $y, $thickness / 2, $color);
        }
    }

    /**
     * @param  list<array{int|float, int|float}>  $points
     */
    private function dashed(array $points, string $color, float $thickness): void
    {
        $dash = 26;
        $gap = 18;

        for ($index = 1; $index < count($points); $index++) {
            [$x1, $y1] = $points[$index - 1];
            [$x2, $y2] = $points[$index];
            $length = hypot($x2 - $x1, $y2 - $y1);

            for ($start = 0; $start < $length; $start += $dash + $gap) {
                $end = min($length, $start + $dash);
                $this->line([
                    [$x1 + ($x2 - $x1) * $start / $length, $y1 + ($y2 - $y1) * $start / $length],
                    [$x1 + ($x2 - $x1) * $end / $length, $y1 + ($y2 - $y1) * $end / $length],
                ], $color, $thickness);
            }
        }
    }

    /** An ellipse outline, as a ring of short lines (GD draws ellipses one pixel thick). */
    private function ring(float $x, float $y, float $radiusX, float $radiusY, string $color, float $thickness): void
    {
        $points = [];

        for ($step = 0; $step <= 96; $step++) {
            $angle = 2 * M_PI * $step / 96;
            $points[] = [$x + $radiusX * cos($angle), $y + $radiusY * sin($angle)];
        }

        $this->line($points, $color, $thickness);
    }

    private function check(float $x1, float $y1, float $x2, float $y2, float $x3, float $y3, string $color, float $thickness): void
    {
        $this->line([[$x1, $y1], [$x2, $y2], [$x3, $y3]], $color, $thickness);
    }

    /** A four-pointed star. */
    private function sparkle(float $x, float $y, float $radius, string $color): void
    {
        $inner = $radius * 0.3;
        $points = [];

        for ($step = 0; $step < 8; $step++) {
            $angle = M_PI / 4 * $step - M_PI / 2;
            $length = $step % 2 === 0 ? $radius : $inner;
            $points[] = [$x + $length * cos($angle), $y + $length * sin($angle)];
        }

        $this->polygon($points, $color);
    }

    // ------------------------------------------------------------------ Backdrop and shadows

    /** Marks where a shape's shadow falls (a little below it). */
    private function shadow(float $x, float $y, float $width, float $height): void
    {
        if (! $this->collecting) {
            return;
        }

        $unit = self::BACKDROP;
        imagefilledrectangle(
            $this->shadows,
            (int) round(($x + 10) / $unit),
            (int) round(($y + 28) / $unit),
            (int) round(($x + $width - 10) / $unit),
            (int) round(($y + $height + 22) / $unit),
            $this->pack([255, 255, 255]),
        );
    }

    /** Blurs the marked shadows and darkens the backdrop under them, by up to $strength. */
    private function castShadows(float $strength): void
    {
        for ($pass = 0; $pass < 7; $pass++) {
            imagefilter($this->shadows, IMG_FILTER_GAUSSIAN_BLUR);
        }

        $tint = self::COLORS['primary'];

        for ($row = 0; $row < imagesy($this->backdrop); $row++) {
            for ($column = 0; $column < imagesx($this->backdrop); $column++) {
                $amount = ((imagecolorat($this->shadows, $column, $row) >> 16) & 0xFF) / 255;

                if ($amount > 0.004) {
                    $base = imagecolorat($this->backdrop, $column, $row);
                    $color = $this->mix([($base >> 16) & 0xFF, ($base >> 8) & 0xFF, $base & 0xFF], $tint, $strength * $amount);
                    imagesetpixel($this->backdrop, $column, $row, $this->pack($color));
                }
            }
        }
    }

    /** The background: a soft gradient lit by two glows, at a fraction of the picture's size. */
    private function backdrop(bool $dark): GdImage
    {
        $width = intdiv(self::WIDTH, self::BACKDROP);
        $height = intdiv(self::HEIGHT, self::BACKDROP);
        $image = $this->image($width, $height);

        [$from, $to] = $dark ? [self::COLORS['night'], self::COLORS['violetDeep']] : [self::COLORS['lavender'], self::COLORS['blush']];
        $glows = $dark
            ? [[0.82, 0.1, 0.55, self::COLORS['violet'], 0.75], [0.12, 0.95, 0.5, self::COLORS['orchid'], 0.35]]
            : [[0.86, 0.12, 0.5, self::COLORS['orchid'], 0.38], [0.1, 0.9, 0.55, self::COLORS['purple300'], 0.6]];

        for ($row = 0; $row < $height; $row++) {
            for ($column = 0; $column < $width; $column++) {
                $u = $column / ($width - 1);
                $v = $row / ($height - 1);
                $color = $this->mix($from, $to, $dark ? 0.35 * $u + 0.65 * $v : 0.6 * $u + 0.4 * $v);

                foreach ($glows as [$gx, $gy, $radius, $glow, $strength]) {
                    $distance = hypot(($u - $gx) * 1.6, $v - $gy) / $radius;

                    if ($distance < 1) {
                        $color = $this->mix($color, $glow, $strength * (1 - $distance) ** 2);
                    }
                }

                imagesetpixel($image, $column, $row, $this->pack($color));
            }
        }

        return $image;
    }

    // ------------------------------------------------------------------ Helpers

    private function image(int $width, int $height): GdImage
    {
        $image = imagecreatetruecolor(max(1, $width), max(1, $height));

        if ($image === false) {
            throw new InvalidArgumentException('The image could not be created.');
        }

        imagealphablending($image, true);

        return $image;
    }

    private function scaled(GdImage $image, int $width, int $height): GdImage
    {
        $scaled = imagescale($image, $width, $height, IMG_BILINEAR_FIXED);

        if ($scaled === false) {
            throw new InvalidArgumentException('The image could not be scaled.');
        }

        imagealphablending($scaled, true);

        return $scaled;
    }

    /** A colour of the palette, optionally transparent (0 opaque … 127 invisible). */
    private function color(string $name, int $alpha = 0): int
    {
        return ($alpha << 24) | $this->pack(self::COLORS[$name] ?? throw new InvalidArgumentException("Unknown colour [{$name}]."));
    }

    /**
     * @param  array{int, int, int}  $rgb
     */
    private function pack(array $rgb): int
    {
        return ($rgb[0] << 16) | ($rgb[1] << 8) | $rgb[2];
    }

    /**
     * @param  array{int, int, int}  $from
     * @param  array{int, int, int}  $to
     * @return array{int, int, int}
     */
    private function mix(array $from, array $to, float $amount): array
    {
        $amount = max(0.0, min(1.0, $amount));

        return [
            (int) round($from[0] + ($to[0] - $from[0]) * $amount),
            (int) round($from[1] + ($to[1] - $from[1]) * $amount),
            (int) round($from[2] + ($to[2] - $from[2]) * $amount),
        ];
    }
}
