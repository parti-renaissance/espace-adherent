@api
@renaissance_api
Feature:
    In order to display video content in client apps
    As a public consumer
    I should be able to fetch a video's metadata by UUID

    Scenario: I get the JSON of a READY portrait 1080p video
        When I send a "GET" request to "/api/videos/550e8400-e29b-41d4-a716-446655440000"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "uuid": "550e8400-e29b-41d4-a716-446655440000",
                "title": "POC — Portrait 9:16 1080p",
                "hls_url": "https://medias.renaissance.code/videos/case_1_9x16_1080/master.m3u8",
                "preview_url": "https://medias.renaissance.code/videos/case_1_9x16_1080/preview.mp4",
                "thumbnail_url": "https://medias.renaissance.code/videos/case_1_9x16_1080/thumbnail0000000000.jpeg",
                "duration": 7,
                "width": 1080,
                "height": 1920
            }
            """

    Scenario: I get the JSON of a READY square 1080p video
        When I send a "GET" request to "/api/videos/550e8400-e29b-41d4-a716-446655440002"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "uuid": "550e8400-e29b-41d4-a716-446655440002",
                "title": "POC — Carré 1:1 1080p",
                "hls_url": "https://medias.renaissance.code/videos/case_3_1x1_1080/master.m3u8",
                "preview_url": "https://medias.renaissance.code/videos/case_3_1x1_1080/preview.mp4",
                "thumbnail_url": "https://medias.renaissance.code/videos/case_3_1x1_1080/thumbnail0000000000.jpeg",
                "duration": 61,
                "width": 1080,
                "height": 1080
            }
            """

    Scenario: I get a 404 when the video UUID does not exist
        When I send a "GET" request to "/api/videos/00000000-0000-0000-0000-000000000000"
        Then the response status code should be 404

    Scenario: I get a 404 when the video is not yet READY (PENDING)
        When I send a "GET" request to "/api/videos/550e8400-e29b-41d4-a716-446655440099"
        Then the response status code should be 404
