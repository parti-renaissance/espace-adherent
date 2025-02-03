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
