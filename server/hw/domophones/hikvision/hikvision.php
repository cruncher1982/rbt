<?php

    namespace hw\domophones {

        require_once __DIR__ . '/../domophones.php';

        abstract class hikvision extends domophones {

            public $user = 'admin';

            protected $api_prefix = '/ISAPI/';
            protected $def_pass = 'admin';

            protected function api_call($resource, $method = 'GET', $params = [], $payload = null) {
                $req = $this->url . $this->api_prefix . $resource;

                if ($params) {
                    $req .= '?' . http_build_query($params);
                }

                echo $method.'   '.$req.'   '.$payload . PHP_EOL;

                $ch = curl_init($req);

                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANYSAFE);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                curl_setopt($ch, CURLOPT_USERPWD, "$this->user:$this->pass");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_VERBOSE, false);

                if ($payload) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                }

                $res = curl_exec($ch);
                curl_close($ch);

                $xml_str = simplexml_load_string($res);
                return json_decode(json_encode($xml_str), true);
            }

            public function add_rfid(string $code) {
                // TODO: Implement add_rfid() method.
            }

            public function clear_apartment(int $apartment = -1) {
                // TODO: Implement clear_apartment() method.
            }

            public function clear_rfid(string $code = '') {
                // TODO: Implement clear_rfid() method.
            }

            public function configure_apartment(
                int $apartment,
                bool $private_code_enabled,
                bool $cms_handset_enabled,
                array $sip_numbers = [],
                int $private_code = 0,
                array $levels = []
            ) {
                // TODO: Implement configure_apartment() method.
            }

            public function configure_cms(int $apartment, int $offset) {
                // не используется
            }

            public function configure_cms_raw(int $index, int $dozens, int $units, int $apartment, string $cms_model) {
                // не используется
            }

            public function configure_gate(array $links) {
                // не используется
            }

            public function configure_md(
                int $sensitivity,
                int $left = 0,
                int $top = 0,
                int $width = 0,
                int $height = 0
            ) {
                // TODO: Implement configure_md() method.
            }

            public function configure_ntp(string $server, int $port, string $timezone) {
                $this->api_call(
                    'System/time',
                    'PUT',
                    [],
                    '<Time>
                                <timeMode>NTP</timeMode>
                                <timeZone>CST-3:00:00</timeZone>
                             </Time>'
                );
                $this->api_call(
                    'System/time/ntpServers/1',
                    'PUT',
                    [],
                    "<NTPServer>
                                <id>1</id>
                                <addressingFormatType>ipaddress</addressingFormatType>
                                <ipAddress>$server</ipAddress>
                                <portNo>$port</portNo>
                                <synchronizeInterval>60</synchronizeInterval>
                            </NTPServer>"
                );
            }

            public function configure_sip(
                string $login,
                string $password,
                string $server,
                int $port = 5060,
                bool $nat = false,
                string $stun_server = '',
                int $stun_port = 3478
            ) {
                $this->api_call(
                    'System/Network/SIP',
                    'PUT',
                    [],
                    "<SIPServerList>
                                <SIPServer>
                                    <id>1</id>
                                    <Standard>
                                        <enabled>true</enabled>
                                        <proxy>$server</proxy>
                                        <proxyPort>$port</proxyPort>
                                        <displayName>$login</displayName>
                                        <userName>$login</userName>
                                        <authID>$login</authID>
                                        <password>$password</password>
                                        <expires>30</expires>
                                    </Standard>
                                </SIPServer>
                            </SIPServerList>"
                );
            }

            public function configure_syslog(string $server, int $port) {
                $this->api_call(
                    'Event/notification/httpHosts',
                    'PUT',
                    [],
                    "<HttpHostNotificationList>
                                <HttpHostNotification>
                                    <id>1</id>
                                    <url>/</url>
                                    <protocolType>HTTP</protocolType>
                                    <parameterFormatType>XML</parameterFormatType>
                                    <addressingFormatType>ipaddress</addressingFormatType>
                                    <ipAddress>$server</ipAddress>
                                    <portNo>$port</portNo>
                                    <httpAuthenticationMethod>none</httpAuthenticationMethod>
                                </HttpHostNotification>
                            </HttpHostNotificationList>"
                );
            }

            public function configure_user_account(string $password) {
                // не используется
            }

            public function configure_video_encoding() {
                $this->api_call(
                    'Streaming/channels/101',
                    'PUT',
                    [],
                    '<StreamingChannel>
                                <id>101</id>
                                <channelName>Camera 01</channelName>
                                <enabled>true</enabled>
                                <Transport>
                                    <ControlProtocolList>
                                        <ControlProtocol>
                                            <streamingTransport>RTSP</streamingTransport>
                                        </ControlProtocol>
                                        <ControlProtocol>
                                            <streamingTransport>HTTP</streamingTransport>
                                        </ControlProtocol>
                                    </ControlProtocolList>
                                    <Security>
                                        <enabled>true</enabled>
                                    </Security>
                                </Transport>
                                <Video>
                                    <enabled>true</enabled>
                                    <videoInputChannelID>1</videoInputChannelID>
                                    <videoCodecType>H.264</videoCodecType>
                                    <videoScanType>progressive</videoScanType>
                                    <videoResolutionWidth>1280</videoResolutionWidth>
                                    <videoResolutionHeight>720</videoResolutionHeight>
                                    <videoQualityControlType>VBR</videoQualityControlType>
                                    <constantBitRate>2048</constantBitRate>
                                    <fixedQuality>60</fixedQuality>
                                    <vbrUpperCap>1024</vbrUpperCap>
                                    <vbrLowerCap>32</vbrLowerCap>
                                    <maxFrameRate>2500</maxFrameRate>
                                    <keyFrameInterval>2000</keyFrameInterval>
                                    <snapShotImageType>JPEG</snapShotImageType>
                                    <GovLength>50</GovLength>
                                </Video>
                                <Audio>
                                    <enabled>true</enabled>
                                    <audioInputChannelID>1</audioInputChannelID>
                                    <audioCompressionType>G.711ulaw</audioCompressionType>
                                </Audio>
                            </StreamingChannel>'
                );

                $this->api_call(
                    'Streaming/channels/102',
                    'PUT',
                    [],
                    '<StreamingChannel>
                                <id>102</id>
                                <channelName>Camera 01</channelName>
                                <enabled>true</enabled>
                                <Transport>
                                    <ControlProtocolList>
                                        <ControlProtocol>
                                            <streamingTransport>RTSP</streamingTransport>
                                        </ControlProtocol>
                                        <ControlProtocol>
                                            <streamingTransport>HTTP</streamingTransport>
                                        </ControlProtocol>
                                    </ControlProtocolList>
                                    <Security>
                                        <enabled>true</enabled>
                                    </Security>
                                </Transport>
                                <Video>
                                    <enabled>true</enabled>
                                    <videoInputChannelID>1</videoInputChannelID>
                                    <videoCodecType>H.264</videoCodecType>
                                    <videoScanType>progressive</videoScanType>
                                    <videoResolutionWidth>704</videoResolutionWidth>
                                    <videoResolutionHeight>576</videoResolutionHeight>
                                    <videoQualityControlType>VBR</videoQualityControlType>
                                    <constantBitRate>512</constantBitRate>
                                    <fixedQuality>60</fixedQuality>
                                    <vbrUpperCap>348</vbrUpperCap>
                                    <vbrLowerCap>32</vbrLowerCap>
                                    <maxFrameRate>2500</maxFrameRate>
                                    <keyFrameInterval>2000</keyFrameInterval>
                                    <snapShotImageType>JPEG</snapShotImageType>
                                    <GovLength>50</GovLength>
                                </Video>
                                <Audio>
                                    <enabled>true</enabled>
                                    <audioInputChannelID>1</audioInputChannelID>
                                    <audioCompressionType>G.711ulaw</audioCompressionType>
                                </Audio>
                            </StreamingChannel>'
                );
            }

            public function enable_public_code(bool $enabled = true) {
                // не используется
            }

            public function get_audio_levels(): array {
                return [];
            }

            public function get_cms_allocation(): array {
                return [];
            }

            public function get_cms_levels(): array {
                return [];
            }

            public function get_rfids(): array {
                return [];
            }

            public function get_sysinfo(): array {
                $res = $this->api_call('System/deviceInfo');

                $sysinfo['DeviceID'] = $res['deviceID'];
                $sysinfo['DeviceModel'] = $res['model'];
                $sysinfo['HardwareVersion'] = $res['hardwareVersion'];
                $sysinfo['SoftwareVersion'] = $res['firmwareVersion'] . ' ' . $res['firmwareReleasedDate'];

                return $sysinfo;
            }

            public function keep_doors_unlocked(bool $unlocked = true) {
                $this->api_call(
                    'AccessControl/RemoteControl/door/1',
                    'PUT',
                    [],
                    $unlocked ? '<cmd>alwaysOpen</cmd>' : '<cmd>resume</cmd>'
                );
            }

            public function line_diag(int $apartment) {
                // не используется
            }

            public function open_door(int $door_number = 0) {
                $this->api_call(
                    'AccessControl/RemoteControl/door/' . ($door_number + 1),
                    'PUT',
                    [],
                    '<cmd>open</cmd>'
                );
            }

            public function set_admin_password(string $password) {
                // TODO: Implement set_admin_password() method.
            }

            public function set_audio_levels(array $levels) {
                $levels[0] = @$levels[0] ?: 7;
                $levels[1] = @$levels[1] ?: 7;
                $levels[2] = @$levels[2] ?: 7;

                $this->api_call(
                    'System/Audio/AudioIn/channels/1',
                    'PUT',
                    [],
                    "<AudioIn>
                                <id>1</id>
                                <AudioInVolumelist><AudioInVlome>
                                <type>audioInput</type>
                                <volume>$levels[0]</volume>
                                </AudioInVlome></AudioInVolumelist>
                            </AudioIn>"
                );

                $this->api_call(
                    'System/Audio/AudioOut/channels/1',
                    'PUT',
                    [],
                    "<AudioOut>
                                <id>1</id>
                                <AudioOutVolumelist>
                                    <AudioOutVlome>
                                        <type>audioOutput</type>
                                        <volume>$levels[1]</volume>
                                        <talkVolume>$levels[2]</talkVolume>
                                    </AudioOutVlome>
                                </AudioOutVolumelist>
                            </AudioOut>"
                );
            }

            public function set_call_timeout(int $timeout) {
                $this->api_call(
                    'VideoIntercom/operationTime',
                    'PUT',
                    [],
                    "<OperationTime>
                                <maxRingTime>$timeout</maxRingTime>
                            </OperationTime>"
                );
            }

            public function set_cms_levels(array $levels) {
                // не используется
            }

            public function set_cms_model(string $model = '') {
                // не используется
            }

            public function set_concierge_number(int $number) {
                // не используется
            }

            public function set_display_text(string $text = '') {
                // не используется
            }

            public function set_public_code(int $code) {
                // не используется
            }

            public function set_relay_dtmf(int $relay_1, int $relay_2, int $relay_3) {
                // не используется
            }

            public function set_sos_number(int $number) {
                // не используется
            }

            public function set_talk_timeout(int $timeout) {
                $this->api_call(
                    'VideoIntercom/operationTime',
                    'PUT',
                    [],
                    "<OperationTime>
                                <talkTime>$timeout</talkTime>
                            </OperationTime>"
                );
            }

            public function set_unlock_time(int $time) {
                $this->api_call(
                    'AccessControl/Door/param/1',
                    'PUT',
                    [],
                    "<DoorParam><doorName>Door1</doorName><openDuration>$time</openDuration></DoorParam>"
                );
            }

            public function set_video_overlay(string $title = '') {
                $this->api_call(
                    'System/Video/inputs/channels/1',
                    'PUT',
                    [],
                    "<VideoInputChannel>
                                <id>1</id>
                                <inputPort>1</inputPort>
                                <name>$title</name>
                            </VideoInputChannel>"
                );
                $this->api_call(
                    'System/Video/inputs/channels/1/overlays',
                    'PUT',
                    [],
                    '<VideoOverlay>
                                <DateTimeOverlay>
                                    <enabled>true</enabled>
                                    <positionY>540</positionY>
                                    <positionX>0</positionX>
                                    <dateStyle>MM-DD-YYYY</dateStyle>
                                    <timeStyle>24hour</timeStyle>
                                    <displayWeek>true</displayWeek>
                                </DateTimeOverlay>
                                <channelNameOverlay>
                                    <enabled>true</enabled>
                                    <positionY>700</positionY>
                                    <positionX>0</positionX>
                                </channelNameOverlay>
                            </VideoOverlay>'
                );
            }

            public function set_language(string $lang) {
                switch ($lang) {
                    case 'RU':
                        $language = 'Russian';
                        break;
                    default:
                        $language = 'English';
                        break;
                }
                $this->api_call(
                    'System/DeviceLanguage',
                    'PUT',
                    [],
                    "<DeviceLanguage><language>$language</language></DeviceLanguage>"
                );
            }

            public function write_config() {
                // не используется
            }

            public function prepare() {
                // TODO: Implement prepare() method.
            }

            public function reboot() {
                $this->api_call('System/reboot', 'PUT');
            }

            public function reset() {
                $this->api_call('System/factoryReset', 'PUT', [ 'mode' => 'basic' ]);
            }

        }

    }
