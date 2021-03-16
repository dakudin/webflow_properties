<?php

return [
    'adminEmail' => 'admin@example.com',
    'one_agency' => [
        'webflow_role_type_collection' => 'Role types',
        'webflow_sales_collection' => [
            'collection_name' => 'Property For Sales',
            'property_id_slug' => 'propertyid'
        ],
        'webflow_lettings_collection' => [
            'collection_name' => 'Property For Rents',
            'property_id_slug' => 'propertyid'
        ],
        'webflow_property_status_collection' => 'Property Statuses',
        'webflow_published_to_live' => true,
        'domain_name' => 'oneagencygroup.co.uk',
        'webflow_review_collection' => 'Reviews',
        'webflow_review_stats_collection' => [
            'collection_name' => 'Reviews stats',
            'review_count_slug' => 'total-reviews',
            'review_avg_rating_slug' => 'overall-rating',
            'stat_item_slug' => 'google-reviews',
            'stat_item_name' => 'Google Reviews',
        ],
    ],
    'white_house_clinic' => [
        'webflow_review_collection' => 'Reviews',
        'webflow_review_stats_collection' => [
            'collection_name' => 'Reviews stats',
            'review_count_slug' => 'total-reviews',
            'review_avg_rating_slug' => 'overall-rating',
            'stat_item_slug' => 'google-reviews',
            'stat_item_name' => 'Google Reviews',
        ],
        'webflow_published_to_live' => true,
        'domain_name' => 'whitehouse-clinic.co.uk',
    ],
];
