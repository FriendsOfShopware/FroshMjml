import { MJML_TAGS } from './mjml-tags';

const TAG_SCORE = 1100;
const ATTR_SCORE = 1000;

const attrSet = new Set();
Object.values(MJML_TAGS).forEach((attrs) => attrs.forEach((a) => attrSet.add(a)));

const tagItems = Object.keys(MJML_TAGS).map((tag) => ({
    caption: tag,
    value: tag,
    meta: 'mjml tag',
    score: TAG_SCORE,
}));

const attrItems = [...attrSet].sort().map((attr) => ({
    caption: attr,
    value: attr,
    meta: 'mjml attr',
    score: ATTR_SCORE,
}));

const allItems = [...tagItems, ...attrItems];

export function mjmlCompleter(prefix) {
    if (!prefix) {
        return tagItems;
    }
    const lower = prefix.toLowerCase();
    return allItems.filter((item) => item.value.toLowerCase().includes(lower));
}

export function mergeCompleters(...completers) {
    return (prefix) => {
        const seen = new Set();
        const out = [];
        for (const fn of completers) {
            if (typeof fn !== 'function') continue;
            let items;
            try {
                items = fn(prefix);
            } catch (e) {
                items = [];
            }
            if (!Array.isArray(items)) continue;
            for (const item of items) {
                const key = item.value ?? item.caption;
                if (key && !seen.has(key)) {
                    seen.add(key);
                    out.push(item);
                }
            }
        }
        return out;
    };
}
