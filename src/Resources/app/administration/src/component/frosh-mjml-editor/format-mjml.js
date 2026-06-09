const INDENT_UNIT = '    ';

const BLOCK_TAGS = new Set([
    'mjml',
    'mj-head',
    'mj-body',
    'mj-wrapper',
    'mj-section',
    'mj-group',
    'mj-column',
    'mj-hero',
    'mj-attributes',
    'mj-social',
    'mj-navbar',
    'mj-carousel',
    'mj-accordion',
    'mj-accordion-element',
]);

const INLINE_TAGS = new Set([
    'a',
    'abbr',
    'b',
    'br',
    'cite',
    'code',
    'em',
    'i',
    'mark',
    'q',
    's',
    'small',
    'span',
    'strong',
    'sub',
    'sup',
    'time',
    'u',
    'wbr',
]);

const TWIG_OPENERS = new Set([
    'block',
    'if',
    'for',
    'with',
    'apply',
    'embed',
    'autoescape',
    'spaceless',
    'verbatim',
    'sandbox',
    'mjml',
]);

function twigKeyword(token) {
    const match = token.match(/^\{%-?\s*(\w+)/);
    return match ? match[1] : '';
}

function isBlockSet(token) {
    return /^\{%-?\s*set\b/.test(token) && !token.includes('=');
}

function tokenize(source) {
    const tokenPattern =
        /\{%[\s\S]*?%\}|\{\{[\s\S]*?\}\}|\{#[\s\S]*?#\}|<!--[\s\S]*?-->|<\/[a-zA-Z][\w-]*\s*>|<[a-zA-Z][\w-]*(?:"[^"]*"|'[^']*'|[^'">])*?\/?>/g;

    const tokens = [];
    let lastIndex = 0;
    let match;
    while ((match = tokenPattern.exec(source)) !== null) {
        if (match.index > lastIndex) {
            tokens.push(source.slice(lastIndex, match.index));
        }
        tokens.push(match[0]);
        lastIndex = tokenPattern.lastIndex;
    }
    if (lastIndex < source.length) {
        tokens.push(source.slice(lastIndex));
    }
    return tokens;
}

function tagName(token) {
    return (token.match(/^<\/?([a-zA-Z][\w-]*)/) || [])[1]?.toLowerCase() ?? '';
}

function isInline(token) {
    const trimmed = token.trim();
    if (trimmed === '') {
        return true;
    }
    if (trimmed.startsWith('{{') || trimmed.startsWith('{#')) {
        return true;
    }
    if (trimmed.startsWith('{%') || trimmed.startsWith('<!--')) {
        return false;
    }
    if (trimmed.startsWith('<')) {
        return INLINE_TAGS.has(tagName(trimmed));
    }
    return true;
}

function glueLeaf(tokens, startIndex, tag) {
    const closePattern = new RegExp('^</' + tag + '\\s*>$', 'i');
    const parts = [tokens[startIndex]];
    let index = startIndex + 1;
    for (; index < tokens.length; index++) {
        parts.push(tokens[index]);
        if (closePattern.test(tokens[index].trim())) {
            break;
        }
    }
    const text = parts
        .join('')
        .replace(/\s*\n\s*/g, ' ')
        .replace(/[ \t]{2,}/g, ' ')
        .trim();
    return { text, endIndex: index };
}

/**
 * Re-indents MJML + Twig source. Block tags and Twig block constructs control the
 * indentation; runs of text + {{ … }} + inline HTML stay together on one line so
 * prose is not torn apart at every variable.
 */
export function formatMjml(source) {
    const tokens = tokenize(source);
    const lines = [];
    let depth = 0;
    let inline = [];
    let pendingBlank = false;
    const pad = (level = depth) => INDENT_UNIT.repeat(Math.max(0, level));

    // Emit a line, materialising a single pending blank line first. Leading and
    // consecutive blanks are swallowed, so single blank lines survive and runs of
    // them collapse to one.
    const emit = (line) => {
        if (pendingBlank) {
            if (lines.length > 0 && lines[lines.length - 1] !== '') {
                lines.push('');
            }
            pendingBlank = false;
        }
        lines.push(line);
    };

    const flushInline = () => {
        if (inline.length === 0) {
            return;
        }
        const text = inline.join('').replace(/\s+/g, ' ').trim();
        inline = [];
        if (text !== '') {
            emit(pad() + text);
        }
    };

    for (let i = 0; i < tokens.length; i++) {
        const token = tokens[i];

        // Whitespace-only gap between tokens: a blank line (>= 2 newlines) is kept
        // as a separator; ordinary whitespace stays part of the inline run.
        if (token.trim() === '') {
            if (/\n[ \t]*\n/.test(token)) {
                flushInline();
                pendingBlank = true;
            } else {
                inline.push(token);
            }
            continue;
        }

        if (isInline(token)) {
            inline.push(token);
            continue;
        }

        flushInline();
        const trimmed = token.trim();

        if (trimmed.startsWith('{%')) {
            const keyword = twigKeyword(trimmed);
            if (keyword.startsWith('end')) {
                depth -= 1;
                emit(pad() + trimmed);
            } else if (keyword === 'else' || keyword === 'elseif') {
                emit(pad(depth - 1) + trimmed);
            } else if (TWIG_OPENERS.has(keyword) || isBlockSet(trimmed)) {
                emit(pad() + trimmed);
                depth += 1;
            } else {
                emit(pad() + trimmed);
            }
            continue;
        }

        if (trimmed.startsWith('<!--')) {
            emit(pad() + trimmed);
            continue;
        }

        if (trimmed.startsWith('</')) {
            depth -= 1;
            emit(pad() + trimmed);
            continue;
        }

        const tag = tagName(trimmed);
        if (/\/>$/.test(trimmed)) {
            emit(pad() + trimmed);
        } else if (BLOCK_TAGS.has(tag)) {
            emit(pad() + trimmed);
            depth += 1;
        } else {
            const glued = glueLeaf(tokens, i, tag);
            emit(pad() + glued.text);
            i = glued.endIndex;
        }
    }

    flushInline();
    return lines.join('\n');
}
