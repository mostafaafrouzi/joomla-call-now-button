import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
text = (ROOT / "mod_callnowbutton/admin/fields/iconselector.php").read_text(encoding="utf-8")
match = re.search(r"\$icons = \[(.*?)\];\s*\n\s*// Build HTML", text, re.S)
if not match:
    raise SystemExit("icons block not found")

block = match.group(1)
icons = {}
for key_match in re.finditer(r"'(\w+)'\s*=>\s*\[", block):
    key = key_match.group(1)
    start = key_match.end()
    label_match = re.search(r"'label'\s*=>\s*'((?:\\'|[^'])*)'", block[start : start + 500])
    svg_match = re.search(r"'svg'\s*=>\s*'((?:\\'|[^'])*)'", block[start : start + 5000])
    if label_match and svg_match:
        icons[key] = {
            "label": label_match.group(1).replace("\\'", "'"),
            "svg": svg_match.group(1).replace("\\'", "'"),
        }

out = ROOT / "mod_callnowbutton/src/Site/Helper/IconRepository.php"
lines = [
    "<?php",
    "/**",
    " * @package     Call Now Button",
    " * @subpackage  mod_callnowbutton",
    " * @copyright   Copyright (C) 2024 Mostafa Afrouzi. All rights reserved.",
    " * @license     GNU General Public License version 2 or later; see LICENSE.txt",
    " */",
    "",
    "namespace Joomla\\Module\\CallNowButton\\Site\\Helper;",
    "",
    "defined('_JEXEC') or die;",
    "",
    "/**",
    " * Central icon definitions for admin selector and front-end rendering.",
    " *",
    " * @since  1.1.0",
    " */",
    "class IconRepository",
    "{",
    "    /** @var array<string, array{label:string,svg:string}>|null */",
    "    protected static $icons = null;",
    "",
    "    /**",
    "     * @return array<string, array{label:string,svg:string}>",
    "     */",
    "    public static function getIcons()",
    "    {",
    "        if (self::$icons === null) {",
    "            self::$icons = [",
]

for key, data in icons.items():
    label = data["label"].replace("'", "\\'")
    svg = data["svg"].replace("'", "\\'")
    lines.append(f"                '{key}' => ['label' => '{label}', 'svg' => '{svg}'],")

lines.extend(
    [
        "            ];",
        "        }",
        "",
        "        return self::$icons;",
        "    }",
        "",
        "    /**",
        "     * Icons formatted for the admin selector field.",
        "     *",
        "     * @return array<string, array{label:string,svg:string}>",
        "     */",
        "    public static function getSelectorIcons()",
        "    {",
        "        return self::getIcons();",
        "    }",
        "",
        "    /**",
        "     * @param   string  $iconType  Icon key",
        "     *",
        "     * @return  boolean",
        "     */",
        "    public static function isValid($iconType)",
        "    {",
        "        return isset(self::getIcons()[(string) $iconType]);",
        "    }",
        "",
        "    /**",
        "     * Render colored SVG markup for the front-end.",
        "     *",
        "     * @param   string   $iconType  Icon key",
        "     * @param   string   $color     Fill color",
        "     * @param   integer  $size      Width/height in pixels",
        "     *",
        "     * @return  string",
        "     */",
        "    public static function render($iconType, $color = '#FFFFFF', $size = 24)",
        "    {",
        "        $icons = self::getIcons();",
        "        $iconType = self::isValid($iconType) ? (string) $iconType : 'phone';",
        "        $svg = $icons[$iconType]['svg'];",
        "        $size = (int) $size;",
        "        $svg = preg_replace('/width=\"\\d+\"/', 'width=\"' . $size . '\"', $svg, 1);",
        "        $svg = preg_replace('/height=\"\\d+\"/', 'height=\"' . $size . '\"', $svg, 1);",
        "        $safeColor = htmlspecialchars((string) $color, ENT_QUOTES, 'UTF-8');",
        "",
        "        return str_replace('fill=\"currentColor\"', 'fill=\"' . $safeColor . '\"', $svg);",
        "    }",
        "}",
        "",
    ]
)

out.write_text("\n".join(lines), encoding="utf-8")
print(f"Generated {out} with {len(icons)} icons")
