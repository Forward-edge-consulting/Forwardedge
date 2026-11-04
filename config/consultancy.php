<?php

return [
    // Bookings are handled in-house: Admin > Bookings > Availability & calendar.
    // YouTube video shown (muted, autoplaying) in the hero. Empty hides the video and shows the photo.
    'video_id' => env('CONSULTANCY_VIDEO_ID', 'GO-uoVM3MV8'),
    // Form builder slug for the discovery call request (Admin > Forms).
    'form_slug' => 'security-discovery-call',
    'consultant' => 'Moyosore Kukoyi',
    'professional_name' => 'CyberMo',

    // Optional, shown only when set. Real material only: nothing is rendered by default.
    // Path of a portrait under /public, e.g. frontend/assets/images/team/moyosore.jpg
    'photo' => env('CONSULTANCY_PHOTO', 'frontend/assets/images/team/moyosore.jpg'),
    // Verifiable facts about the consultant, e.g. ['CISSP', 'ISO 27001 Lead Implementer'].
    'credentials' => [],
];
