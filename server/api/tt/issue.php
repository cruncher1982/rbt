<?php

    /**
     * tt api
     */

    namespace api\tt {

        use api\api;

        /**
         * tt (task tracker metadata(s)) method
         */

        class issue extends api {

            public static function GET($params) {
                $tt = loadBackend("tt");

                if (!$tt) {
                    return API::ERROR(500);
                }

                $issue = $tt->getIssue($params["_id"]);

                if (!$issue) {
                    return API::ERROR(404);
                }

                $workflow = $tt->loadWorkflow($issue["workflow"]);

                if (!$workflow) {
                    return API::ERROR(404);
                }

                $issue = $workflow->viewIssue($issue);

                return api::ANSWER($issue, "issue");
            }

            public static function POST($params) {
                $tt = loadBackend("tt");

                $id = $tt->loadWorkflow($params["issue"]["workflow"])->createIssue($params["issue"]);

                return api::ANSWER($id, ($id !== false)?"id":false);
            }

            public static function PUT($params) {
//                $tt_resolutions = loadBackend("tt")->getResolutions;
                return api::ANSWER();
            }

            public static function DELETE($params) {
//                $tt_resolutions = loadBackend("tt")->getResolutions;
                return api::ANSWER();
            }

            public static function index() {
                if (loadBackend("tt")) {
                    return [
                        "GET",
                        "POST",
                        "PUT",
                        "DELETE",
                    ];
                } else {
                    return false;
                }
            }
        }
    }
