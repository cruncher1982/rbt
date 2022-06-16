<?php

    /**
     * backends houses namespace
     */

    namespace backends\houses {

        /**
         * internal.db houses class
         */

        class internal extends houses {

            /**
             * @inheritDoc
             */
            function getHouseFlats($houseId)
            {
                if (!checkInt($houseId)) {
                    return false;
                }

                $flats = $this->db->get("select house_flat_id, floor, flat, auto_block, manual_block, open_code, auto_open, white_rabbit, sip_enabled, sip_password from houses_flats where address_house_id = $houseId order by flat",
                    false,
                    [
                        "house_flat_id" => "flatId",
                        "floor" => "floor",
                        "flat" => "flat",
                        "autoBlock" => "autoBlock",
                        "manual_block" => "manualBlock",
                        "open_code" => "openCode",
                        "auto_open" => "autoOpen",
                        "white_rabbit" => "whiteRabbit",
                        "sip_enabled" => "sipEnabled",
                        "sip_password" => "sipPassword",
                    ]
                );

                if ($flats) {
                    foreach ($flats as &$flat) {
                        $entrances = $this->db->get("select house_entrance_id, apartment, cms_levels from houses_entrances_flats where house_flat_id = {$flat["flatId"]}", false, [
                            "house_entrance_id" => "entranceId",
                            "apartment" => "apartment",
                            "cms_levels" => "apartmentLevels",
                        ]);
                        $flat["entrances"] = [];
                        foreach ($entrances as $e) {
                            $flat["entrances"][] = $e;
                        }
                    }
                }

                return $flats;
            }

            /**
             * @inheritDoc
             */
            function getHouseEntrances($houseId)
            {
                if (!checkInt($houseId)) {
                    return false;
                }

                return $this->db->get("select address_house_id, house_entrance_id, entrance_type, entrance, shared, lat, lon, cms_type, prefix from houses_houses_entrances left join houses_entrances using (house_entrance_id) where address_house_id = $houseId order by entrance_type, entrance",
                    false,
                    [
                        "address_house_id" => "houseId",
                        "house_entrance_id" => "entranceId",
                        "entrance_type" => "entranceType",
                        "entrance" => "entrance",
                        "shared" => "shared",
                        "lat" => "lat",
                        "lon" => "lon",
                        "cms_type" => "cmsType",
                        "prefix" => "prefix",
                    ]
                );
            }

            /**
             * @inheritDoc
             */
            function createEntrance($houseId, $entranceType, $entrance, $shared, $lat, $lon, $cmsType, $prefix)
            {
                if (checkInt($houseId) && trim($entranceType) && trim($entrance) && checkInt($cmsType) && checkInt($prefix))
                {
                    $entranceId = $this->db->insert("insert into houses_entrances (entrance_type, entrance, shared, lat, lon, cms_type) values (:entrance_type, :entrance, :shared, :lat, :lon, :cms_type)", [
                        ":entrance_type" => $entranceType,
                        ":entrance" => $entrance,
                        ":shared" => (int)$shared,
                        ":lat" => (float)$lat,
                        ":lon" => (float)$lon,
                        ":cms_type" => $cmsType,
                    ]);

                    if (!$entranceId) {
                        return false;
                    }

                    return $this->db->modify("insert into houses_houses_entrances (address_house_id, house_entrance_id, prefix) values (:address_house_id, :house_entrance_id, :prefix)", [
                        ":address_house_id" => $houseId,
                        ":house_entrance_id" => $entranceId,
                        ":prefix" => $prefix,
                    ]);
                } else {
                    return false;
                }
            }

            /**
             * @inheritDoc
             */
            function addEntrance($houseId, $entranceId, $prefix)
            {
                if (!checkInt($houseId) || !checkInt($entranceId) || !checkInt($prefix)) {
                    return false;
                }

                return $this->db->modify("insert into houses_houses_entrances (address_house_id, house_entrance_id, prefix) values (:address_house_id, :house_entrance_id, :prefix)", [
                    ":address_house_id" => $houseId,
                    ":house_entrance_id" => $entranceId,
                    ":prefix" => $prefix,
                ]);
            }

            /**
             * @inheritDoc
             */
            function modifyEntrance($entranceId, $houseId, $entranceType, $entrance, $shared, $lat, $lon, $cmsType, $prefix)
            {
                error_log(print_r([ $entranceId, $houseId, $entranceType, $entrance, $shared, $lat, $lon, $cmsType, $prefix ], true));
                if (!checkInt($entranceId) || !trim($entranceType) || !trim($entrance) || !checkInt($cmsType) || !checkInt($prefix)) {
                    return false;
                }

                return
                    $this->db->modify("update houses_houses_entrances set prefix = :prefix where house_entrance_id = $entranceId and address_house_id = $houseId", [
                        ":prefix" => $prefix,
                    ]) !== false
                    and
                    $this->db->modify("update houses_entrances set entrance_type = :entrance_type, entrance = :entrance, shared = :shared, lat = :lat, lon = :lon, cms_type = :cms_type where house_entrance_id = $entranceId", [
                        ":entrance_type" => $entranceType,
                        ":entrance" => $entrance,
                        ":shared" => (int)$shared,
                        ":lat" => (float)$lat,
                        ":lon" => (float)$lon,
                        ":cms_type" => $cmsType,
                    ]) !== false;
            }

            /**
             * @inheritDoc
             */
            function deleteEntrance($entranceId, $houseId)
            {
                if (!checkInt($houseId) || !checkInt($entranceId)) {
                    return false;
                }

                return
                    $this->db->modify("delete from houses_houses_entrances where address_house_id = $houseId and house_entrance_id = $entranceId") !== false
                    and
                    $this->db->modify("delete from houses_entrances where house_entrance_id not in (select house_entrance_id from houses_houses_entrances)") !== false
                    and
                    $this->db->modify("delete from houses_entrances_flats where house_entrance_id not in (select house_entrance_id from houses_entrances)") !== false;
            }

            /**
             * @inheritDoc
             */
            function addFlat($houseId, $floor, $flat, $entrances, $apartmentsAndFlats, $manualBlock, $openCode, $autoOpen, $whiteRabbit, $sipEnabled, $sipPassword)
            {
                if (checkInt($houseId) && trim($flat) && checkInt($manualBlock) && checkInt($autoOpen) && checkInt($whiteRabbit) && checkInt($sipEnabled)) {
                    $flatId = $this->db->insert("insert into houses_flats (address_house_id, floor, flat, manual_block, open_code, auto_open, white_rabbit, sip_enabled, sip_password) values (:address_house_id, :floor, :flat, :manual_block, :open_code, :auto_open, :white_rabbit, :sip_enabled, :sip_password)", [
                        ":address_house_id" => $houseId,
                        ":floor" => (int)$floor,
                        ":flat" => $flat,
                        ":manual_block" => $manualBlock,
                        ":open_code" => $openCode,
                        ":auto_open" => $autoOpen,
                        ":white_rabbit" => $whiteRabbit,
                        ":sip_enabled" => $sipEnabled,
                        ":sip_password" => $sipPassword,
                    ]);

                    if ($flatId) {
                        for ($i = 0; $i < count($entrances); $i++) {
                            if (!checkInt($entrances[$i])) {
                                return false;
                            } else {
                                $ap = $flat;
                                $lv = "";
                                if ($apartmentsAndFlats && @$apartmentsAndFlats[$entrances[$i]]) {
                                    $ap = (int)$apartmentsAndFlats[$entrances[$i]]["apartment"];
                                    if (!$ap || $ap <= 0 || $ap > 9999) {
                                        $ap = $flat;
                                    }
                                    $lv = $apartmentsAndFlats[$entrances[$i]]["apartmentLevels"];
                                }
                                if ($this->db->modify("insert into houses_entrances_flats (house_entrance_id, house_flat_id, apartment, cms_levels) values (:house_entrance_id, :house_flat_id, :apartment, :cms_levels)", [
                                        ":house_entrance_id" => $entrances[$i],
                                        ":house_flat_id" => $flatId,
                                        ":apartment" => $ap,
                                        ":cms_levels" => $lv,
                                    ]) === false) {
                                    return false;
                                }
                            }
                        }
                        return $flatId;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }

            /**
             * @inheritDoc
             */
            function modifyFlat($flatId, $floor, $flat, $entrances, $apartmentsAndFlats, $manualBlock, $openCode, $autoOpen, $whiteRabbit, $sipEnabled, $sipPassword)
            {
                if (checkInt($flatId) && trim($flat) && checkInt($manualBlock) && checkInt($autoOpen) && checkInt($whiteRabbit) && checkInt($sipEnabled)) {
                    $mod = $this->db->modify("update houses_flats set floor = :floor, flat = :flat, manual_block = :manual_block, open_code = :open_code, auto_open = :auto_open, white_rabbit = :white_rabbit, sip_enabled = :sip_enabled, sip_password = :sip_password where house_flat_id = $flatId", [
                        ":floor" => (int)$floor,
                        ":flat" => $flat,
                        ":manual_block" => $manualBlock,
                        ":open_code" => $openCode,
                        ":auto_open" => $autoOpen,
                        ":white_rabbit" => $whiteRabbit,
                        ":sip_enabled" => $sipEnabled,
                        ":sip_password" => $sipPassword,
                    ]);

                    if ($mod !== false) {
                        if ($this->db->modify("delete from houses_entrances_flats where house_flat_id = $flatId") === false) {
                            return false;
                        }
                        for ($i = 0; $i < count($entrances); $i++) {
                            if (!checkInt($entrances[$i])) {
                                return false;
                            } else {
                                $ap = $flat;
                                $lv = "";
                                if ($apartmentsAndFlats && @$apartmentsAndFlats[$entrances[$i]]) {
                                    $ap = (int)$apartmentsAndFlats[$entrances[$i]]["apartment"];
                                    if (!$ap || $ap <= 0 || $ap > 9999) {
                                        $ap = $flat;
                                    }
                                    $lv = $apartmentsAndFlats[$entrances[$i]]["apartmentLevels"];
                                }
                                if ($this->db->modify("insert into houses_entrances_flats (house_entrance_id, house_flat_id, apartment, cms_levels) values (:house_entrance_id, :house_flat_id, :apartment, :cms_levels)", [
                                    ":house_entrance_id" => $entrances[$i],
                                    ":house_flat_id" => $flatId,
                                    ":apartment" => $ap,
                                    ":cms_levels" => $lv,
                                ]) === false) {
                                    return false;
                                }
                            }
                        }
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }

            /**
             * @inheritDoc
             */
            function deleteFlat($flatId)
            {
                if (!checkInt($flatId)) {
                    return false;
                }

                return
                    $this->db->modify("delete from houses_flats where house_flat_id = $flatId") !== false
                    and
                    $this->db->modify("delete from houses_entrances_flats where house_flat_id not in (select house_flat_id from houses_flats)") !== false;
            }

            /**
             * @inheritDoc
             */
            function getSharedEntrances($houseId = false)
            {
                if ($houseId && !checkInt($houseId)) {
                    return false;
                }

                if ($houseId) {
                    return $this->db->get("select * from (select house_entrance_id, entrance_type, entrance, lat, lon, (select address_house_id from houses_houses_entrances where houses_houses_entrances.house_entrance_id = houses_entrances.house_entrance_id and address_house_id <> $houseId limit 1) address_house_id from houses_entrances where shared = 1 and house_entrance_id in (select house_entrance_id from houses_houses_entrances where house_entrance_id not in (select house_entrance_id from houses_houses_entrances where address_house_id = $houseId))) as t1 where address_house_id is not null", false, [
                        "house_entrance_id" => "entranceId",
                        "entrance_type" => "entranceType",
                        "entrance" => "entrance",
                        "lat" => "lat",
                        "lon" => "lon",
                        "address_house_id" => "houseId",
                    ]);
                } else {
                    return $this->db->get("select * from (select house_entrance_id, entrance_type, entrance, lat, lon, (select address_house_id from houses_houses_entrances where houses_houses_entrances.house_entrance_id = houses_entrances.house_entrance_id limit 1) address_house_id from houses_entrances where shared = 1) as t1 where address_house_id is not null", false, [
                        "house_entrance_id" => "entranceId",
                        "entrance_type" => "entranceType",
                        "entrance" => "entrance",
                        "lat" => "lat",
                        "lon" => "lon",
                        "address_house_id" => "houseId",
                    ]);
                }
            }

            /**
             * @inheritDoc
             */
            function destroyEntrance($entranceId)
            {
                if (!checkInt($entranceId)) {
                    return false;
                }

                return
                    $this->db->modify("delete from houses_entrances where house_entrance_id = $entranceId") !== false
                    and
                    $this->db->modify("delete from houses_houses_entrances where house_entrance_id not in (select house_entrance_id from houses_entrances)") !== false
                    and
                    $this->db->modify("delete from houses_entrances_flats where house_entrance_id not in (select house_entrance_id from houses_entrances)") !== false;
            }
        }
    }
