@api
Feature:
    In order to display general conventions informations
    As an anonymous user
    I should be able to list and read general conventions

    Scenario: As an anonymous user, I can list general conventions
        And I send a "GET" request to "/api/general_conventions?page_size=10"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "metadata": {
                    "total_items": 3,
                    "items_per_page": 10,
                    "count": 3,
                    "current_page": 1,
                    "last_page": 1
                },
                "items": [
                    {
                        "department_zone": {
                            "uuid": "@uuid@",
                            "type": "department",
                            "code": "92",
                            "name": "Hauts-de-Seine"
                        },
                        "committee": null,
                        "district_zone": null,
                        "organizer": "assembly",
                        "reported_at": "@string@.isDateTime()",
                        "meeting_type": "on_site",
                        "members_count": 0,
                        "participant_quality": "adherent",
                        "uuid": "c5317499-7130-4255-a7f8-418e72f5dfa5"
                    },
                    {
                        "department_zone": {
                            "uuid": "@uuid@",
                            "type": "department",
                            "code": "92",
                            "name": "Hauts-de-Seine"
                        },
                        "committee": {
                            "uuid": "@uuid@",
                            "name": "Comité des 3 communes",
                            "slug": "comite-des-3-communes"
                        },
                        "district_zone": null,
                        "organizer": "committee",
                        "reported_at": "@string@.isDateTime()",
                        "meeting_type": "remote",
                        "members_count": 20,
                        "participant_quality": "sympathizer",
                        "uuid": "b3a2b082-01fc-4306-9fdb-6559ebe765b1"
                    },
                    {
                        "department_zone": {
                            "uuid": "@uuid@",
                            "type": "department",
                            "code": "92",
                            "name": "Hauts-de-Seine"
                        },
                        "committee": null,
                        "district_zone": {
                            "uuid": "@uuid@",
                            "type": "district",
                            "code": "92-4",
                            "name": "Hauts-de-Seine (4)"
                        },
                        "organizer": "district",
                        "reported_at": "@string@.isDateTime()",
                        "meeting_type": "remote",
                        "members_count": 10,
                        "participant_quality": "adherent_before",
                        "uuid": "54c9ae4c-3e2d-475d-8993-54639ec58ea1"
                    }
                ]
            }
            """

    Scenario: As an anonymous user, I can get informations of a general convention
        And I send a "GET" request to "/api/general_conventions/c5317499-7130-4255-a7f8-418e72f5dfa5"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "department_zone": {
                    "uuid": "@uuid@",
                    "type": "department",
                    "code": "92",
                    "name": "Hauts-de-Seine"
                },
                "committee": null,
                "district_zone": null,
                "organizer": "assembly",
                "reported_at": "@string@.isDateTime()",
                "meeting_type": "on_site",
                "members_count": 0,
                "participant_quality": "adherent",
                "general_summary": null,
                "party_definition_summary": null,
                "unique_party_summary": null,
                "progress_since2016": null,
                "party_objectives": null,
                "governance": null,
                "communication": null,
                "militant_training": null,
                "member_journey": null,
                "mobilization": null,
                "talent_detection": null,
                "election_preparation": null,
                "relationship_with_supporters": null,
                "work_with_partners": null,
                "additional_comments": null,
                "uuid": "c5317499-7130-4255-a7f8-418e72f5dfa5"
            }
            """
@debug
    Scenario: As a granted user, I can post general conventions
        Given I am logged with "carl999@example.fr" via OAuth client "Make App" with scopes "write:general_conventions"
        And I send a "POST" request to "/api/v3/general_conventions" with body:
        """
        {
            "department_zone": "e3efe6fd-906e-11eb-a875-0242ac150002",
            "district_zone": "e3f0ee1a-906e-11eb-a875-0242ac150002",
            "committee": "8c4b48ec-9290-47ae-a5db-d1cf2723e8b3",
            "organizer": "assembly",
            "meeting_type": "on_site",
            "participant_quality": "adherent",
            "members_count": 15,
            "reporter": "carl999@example.fr",
            "reported_at": "2025-02-06 09:30:00",
            "general_summary": "Lorem ipsum dolor sit amet",
            "party_definition_summary": "Lorem ipsum dolor sit amet",
            "unique_party_summary": "Lorem ipsum dolor sit amet",
            "progress_since2016": "Lorem ipsum dolor sit amet",
            "party_objectives": "Lorem ipsum dolor sit amet",
            "governance": "Lorem ipsum dolor sit amet",
            "communication": "Lorem ipsum dolor sit amet",
            "militant_training": "Lorem ipsum dolor sit amet",
            "member_journey": "Lorem ipsum dolor sit amet",
            "mobilization": "Lorem ipsum dolor sit amet",
            "talent_detection": "Lorem ipsum dolor sit amet",
            "election_preparation": "Lorem ipsum dolor sit amet",
            "relationship_with_supporters": "Lorem ipsum dolor sit amet",
            "work_with_partners": "Lorem ipsum dolor sit amet",
            "additional_comments": "Lorem ipsum dolor sit amet"
        }
        """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
        """
        {
            "uuid": "@uuid@",
            "department_zone": {
                "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
                "type": "department",
                "code": "92",
                "name": "Hauts-de-Seine"
            },
            "district_zone": {
                "uuid": "e3f0ee1a-906e-11eb-a875-0242ac150002",
                "type": "district",
                "code": "92-5",
                "name": "Hauts-de-Seine (5)"
            },
            "committee": {
                "uuid": "93b72179-7d27-40c4-948c-5188aaf264b6",
                "name": "En Marche - Comité de Singapour",
                "slug": "en-marche-comite-de-singapour"
            },
            "organizer": "assembly",
            "reported_at": "2025-02-06T09:30:00+01:00",
            "meeting_type": "on_site",
            "members_count": 15,
            "participant_quality": "adherent",
            "general_summary": "Lorem ipsum dolor sit amet",
            "party_definition_summary": "Lorem ipsum dolor sit amet",
            "unique_party_summary": "Lorem ipsum dolor sit amet",
            "progress_since2016": "Lorem ipsum dolor sit amet",
            "party_objectives": "Lorem ipsum dolor sit amet",
            "governance": "Lorem ipsum dolor sit amet",
            "communication": "Lorem ipsum dolor sit amet",
            "militant_training": "Lorem ipsum dolor sit amet",
            "member_journey": "Lorem ipsum dolor sit amet",
            "mobilization": "Lorem ipsum dolor sit amet",
            "talent_detection": "Lorem ipsum dolor sit amet",
            "election_preparation": "Lorem ipsum dolor sit amet",
            "relationship_with_supporters": "Lorem ipsum dolor sit amet",
            "work_with_partners": "Lorem ipsum dolor sit amet",
            "additional_comments": "Lorem ipsum dolor sit amet"
        }
        """
