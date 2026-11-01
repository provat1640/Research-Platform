<?php

return [
    'methodologies' => [
        'Qualitative',
        'Quantitative',
        'Mixed methods',
        'Design science',
        'Literature review',
        'Action research',
    ],

    'lifecycle' => [
        [
            'key' => 'initialization',
            'number' => '01',
            'title' => 'Initialization',
            'eyebrow' => 'Plan the inquiry',
            'purpose' => 'Turn an idea into a bounded, methodologically defensible research brief.',
            'outputs' => ['Project overview', 'Research question', 'Method and theory', 'Contribution brief'],
            'modules' => ['Project planning', 'Artifacts', 'Tools and process', 'Method differentiation', 'Integration readiness'],
            'accent' => 'method',
        ],
        [
            'key' => 'presentation',
            'number' => '02',
            'title' => 'Presentation',
            'eyebrow' => 'Build the argument',
            'purpose' => 'Shape evidence into a paper, presentation, and citation-ready research narrative.',
            'outputs' => ['Paper presentation', 'Edited manuscript', 'Citation trail', 'Peer-review draft'],
            'modules' => ['Manuscript editor', 'Citations and bibliography', 'Figures and media', 'AI supplement'],
            'accent' => 'evidence',
        ],
        [
            'key' => 'review',
            'number' => '03',
            'title' => 'Review',
            'eyebrow' => 'Strengthen the claim',
            'purpose' => 'Move feedback through supervisor advice, justification, standards checks, and revision.',
            'outputs' => ['Supervisor handoff', 'Advice and review', 'Merged decisions', 'Standards-fit record'],
            'modules' => ['Feedback desk', 'Peer review', 'Version history', 'Auxiliary pipelines'],
            'accent' => 'review',
        ],
    ],
];
