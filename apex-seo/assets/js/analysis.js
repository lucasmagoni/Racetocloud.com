(function($) {
    'use strict';

    $(document).ready(function() {
        const titleInput = $('#apex_seo_title');
        const descInput = $('#apex_seo_description');
        const keywordInput = $('#apex_seo_focus_keyword');
        const analysisResult = $('#apex-seo-analysis-result');
        const descCounter = $('#apex-seo-desc-counter');

        function getEditorContent() {
            if (typeof wp !== 'undefined' && wp.data && wp.data.select('core/editor')) {
                // Gutenberg
                return wp.data.select('core/editor').getEditedPostContent();
            } else if (typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden()) {
                // TinyMCE
                return tinyMCE.activeEditor.getContent();
            } else {
                // Textarea
                return $('#content').val();
            }
        }

        function countPixels(text) {
            // Approximation: average char width 7px, but uppercase/wide chars vary.
            // For a robust solution, one would render to a hidden canvas.
            // Here we use a simple length check for now as a proxy, or a simple canvas measure.
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            context.font = '14px Arial'; // Google snippet font approx
            return context.measureText(text).width;
        }

        function updateSnippetPreview() {
            const title = titleInput.val();
            const desc = descInput.val();
            const keyword = keywordInput.val();

            // Update pixel counter
            const width = countPixels(desc);
            const maxWidth = 920; // Approx max pixel width for desktop description

            let color = 'green';
            if (width === 0) color = 'black';
            if (width > maxWidth) color = 'red';

            descCounter.text(Math.round(width) + 'px / ' + maxWidth + 'px').css('color', color);

            runAnalysis(title, desc, keyword, getEditorContent());
        }

        function runAnalysis(title, desc, keyword, content) {
            if (!keyword) {
                analysisResult.html('<p>Please enter a focus keyword to analyze.</p>');
                return;
            }

            let score = 0;
            let checks = [];

            // keyword in title
            if (title.toLowerCase().includes(keyword.toLowerCase())) {
                score += 10;
                checks.push('<span style="color:green">✓ Keyword in title</span>');
            } else {
                checks.push('<span style="color:red">✗ Keyword not in title</span>');
            }

            // keyword in description
            if (desc.toLowerCase().includes(keyword.toLowerCase())) {
                score += 10;
                checks.push('<span style="color:green">✓ Keyword in meta description</span>');
            } else {
                checks.push('<span style="color:red">✗ Keyword not in meta description</span>');
            }

            // content analysis
            if (content) {
                // stripping tags
                const textContent = content.replace(/(<([^>]+)>)/gi, "");

                // keyword density
                const wordCount = textContent.split(/\s+/).length;
                const keywordCount = (textContent.toLowerCase().match(new RegExp(keyword.toLowerCase(), "g")) || []).length;
                const density = (keywordCount / wordCount) * 100;

                if (density > 0.5 && density < 2.5) {
                    score += 10;
                    checks.push('<span style="color:green">✓ Good keyword density (' + density.toFixed(2) + '%)</span>');
                } else {
                    checks.push('<span style="color:orange">⚠ Keyword density is ' + density.toFixed(2) + '% (Aim for 0.5-2.5%)</span>');
                }

                // Flesch Reading Ease
                const sentenceCount = textContent.split(/[.!?]+/).length;
                const syllableCount = countSyllables(textContent);
                const flesch = 206.835 - (1.015 * (wordCount / sentenceCount)) - (84.6 * (syllableCount / wordCount));

                let fleschMsg = 'Difficult';
                if (flesch > 60) fleschMsg = 'Standard';
                if (flesch > 80) fleschMsg = 'Easy';

                checks.push('<span>Reading Score: ' + Math.round(flesch) + ' (' + fleschMsg + ')</span>');
            }

            analysisResult.html(checks.join('<br>'));
        }

        function countSyllables(text) {
            const words = text.toLowerCase().split(/\s+/);
            let totalSyllables = 0;

            words.forEach(function(word) {
                if(word.length <= 3) {
                    totalSyllables += 1;
                    return;
                }
                word = word.replace(/(?:[^laeiouy]es|ed|[^laeiouy]e)$/, '');
                word = word.replace(/^y/, '');
                const matches = word.match(/[aeiouy]{1,2}/g);
                totalSyllables += matches ? matches.length : 1;
            });

            return totalSyllables;
        }

        // Listeners
        titleInput.on('input', updateSnippetPreview);
        descInput.on('input', updateSnippetPreview);
        keywordInput.on('input', updateSnippetPreview);

        // Initial run
        if (typeof wp !== 'undefined' && wp.data) {
             wp.data.subscribe(function() {
                 updateSnippetPreview();
             });
        }

        // Polling for tinyMCE changes if not Gutenberg
        setInterval(updateSnippetPreview, 2000);

    });

})(jQuery);
