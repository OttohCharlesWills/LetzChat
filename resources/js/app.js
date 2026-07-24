import "@hotwired/turbo";
import twemoji from 'twemoji';
import './bootstrap';

window.twemoji = twemoji;

document.addEventListener('turbo:load', function () {
    twemoji.parse(document.body, {
        // Twemoji's own asset-fetching logic is being reused here purely as
        // the "find emoji characters and swap them for <img> tags" engine —
        // but the actual images it fetches are Google's Noto Color Emoji set
        // instead of Twemoji's default Twitter-style SVGs. Noto's filenames
        // use underscores (emoji_u1f600.svg) where Twemoji's use hyphens
        // (1f600.svg), so a custom callback builds the right URL.
        callback: function (icon) {
            return `https://cdn.jsdelivr.net/gh/googlefonts/noto-emoji/svg/emoji_u${icon.replace(/-/g, '_')}.svg`;
        },
    });
});