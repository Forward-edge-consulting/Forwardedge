<?php

return [
    'site_name' => env('APP_NAME', 'Forward Edge Consulting'),

    'defaults' => [
        'title'       => 'Forward Edge Consulting | Cybersecurity Training & Advisory',
        'description' => 'Forward Edge Consulting delivers hands-on cybersecurity training, professional services, and industry events that help African talent build world-class careers.',
        'keywords'    => implode(', ', [
            'Forward Edge Consulting',
            'cybersecurity training africa',
            'cybersecurity training nigeria',
            'ethical hacking bootcamp',
            'blue team vs red team',
            'governance risk compliance services',
            'how to start a cybersecurity career',
            'top cybersecurity certifications for beginners',
            'SOC analyst training',
            'cloud security consulting',
            'vulnerability management services',
            'penetration testing workshop',
            'cybersecurity interview questions',
            'incident response best practices',
            'secure software development training',
            'cyber awareness for executives',
            'data privacy compliance africa',
            'questions businesses ask about cybersecurity'
        ]),
        'image'       => env('SEO_DEFAULT_IMAGE', 'frontend/assets/images/logos/logo.png'),
    ],

    'routes' => [
        'home' => [
            'title'       => 'Forward Edge Consulting | Build In-Demand Cybersecurity Skills',
            'description' => 'Explore expert-led cybersecurity programs, career-launching events, and consulting support trusted by leading African organizations.',
        ],
        'about' => [
            'title'       => 'About Forward Edge Consulting | Driving Africa’s Cyber Talent',
            'description' => 'Meet the cybersecurity leaders, instructors, and strategists empowering businesses and talent across Africa.',
        ],
        'consultancy' => [
            'title' => 'Information Security Consulting | Forward Edge Consulting',
            'description' => 'Speak with Moyosore Kukoyi (CyberMo) about practical information security guidance for your organisation. Request a free discovery call.',
        ],
        'contact' => [
            'title'       => 'Contact Forward Edge Consulting',
            'description' => 'Talk to our advisory team about cybersecurity training, events, partnerships, or enterprise consulting support.',
        ],
        'services' => [
            'title'       => 'Cybersecurity Services | Forward Edge Consulting',
            'description' => 'Partner with our consultants for governance, risk & compliance, SOC build-outs, managed security, and transformation programs.',
        ],
        'services.show' => [
            'description' => 'See how this Forward Edge Consulting service helps teams strengthen cybersecurity posture and unlock new capabilities.',
        ],
        'blog' => [
            'title'       => 'Insights & Resources | Forward Edge Consulting Blog',
            'description' => 'Read expert takes on cybersecurity, digital skills, compliance, and tech leadership across Africa.',
        ],
        'blogs.show' => [
            'description' => 'Actionable cybersecurity guidance, event recaps, and skill-building advice from Forward Edge consultants.',
        ],
        'events.index' => [
            'title'       => 'Events & Training | Forward Edge Consulting',
            'description' => 'Register for cybersecurity workshops, leadership roundtables, and immersive bootcamps hosted across Africa.',
        ],
        'events.upcoming' => [
            'description' => 'Secure a seat at our upcoming cybersecurity training events before they sell out.',
        ],
        'events.featured' => [
            'description' => 'Don’t miss our flagship cybersecurity events spotlighting emerging threats and best practices.',
        ],
        'events.show' => [
            'description' => 'Agenda, speakers, and ticket info for this Forward Edge Consulting event or training program.',
        ],
        'gallery' => [
            'title'       => 'Forward Edge Consulting Gallery',
            'description' => 'Highlights from cybersecurity bootcamps, corporate trainings, and community programs.',
        ],
        'page.show' => [
            'description' => 'Explore curated resources, landing pages, and campaign experiences from Forward Edge Consulting.',
        ],
    ],
];
