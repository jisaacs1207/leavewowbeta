# Third-party notices

## classic-wow-name-filter (curated reference only)

A small curated English subset of high-severity abuse terms was reviewed against
[`namesprofanity.csv`](https://github.com/classic-wow-name-filter/classic-wow-name-filter/blob/master/namesprofanity.csv)
from [classic-wow-name-filter](https://github.com/classic-wow-name-filter/classic-wow-name-filter)
and merged into `includes/name_filter.php` where it filled gaps.

We do **not** vendor or ship the full CSV (multilingual Blizzard `namesprofanity`
DBC dump via wow.tools; many short/incomplete patterns cause fantasy-name false
positives when applied naively).

Upstream license (MIT):

```
MIT License

Copyright (c) 2019 classic-wow-name-filter

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

README provenance note from upstream: database taken from wow.tools
(`dbc=namesprofanity`).
