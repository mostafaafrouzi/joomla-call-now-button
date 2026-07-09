import re
import json
from pathlib import Path

text = Path("mod_callnowbutton/admin/fields/iconselector.php").read_text(encoding="utf-8")
match = re.search(r"\$icons = \[(.*?)\];\s*\n\s*// Build HTML", text, re.S)
if not match:
    raise SystemExit("icons block not found")

block = match.group(1)
icons = {}
for key_match in re.finditer(r"'(\w+)'\s*=>\s*\[", block):
    key = key_match.group(1)
    start = key_match.end()
    label_match = re.search(r"'label'\s*=>\s*'((?:\\'|[^'])*)'", block[start:start + 500])
    svg_match = re.search(r"'svg'\s*=>\s*'((?:\\'|[^'])*)'", block[start:start + 5000])
    if not label_match or not svg_match:
        continue
    label = label_match.group(1).replace("\\'", "'")
    svg = svg_match.group(1).replace("\\'", "'")
    icons[key] = {"label": label, "svg": svg}

print(json.dumps(icons, ensure_ascii=False, indent=2))
