<?php
/**
 * Copyright 2020 Google LLC.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Google\Cloud\Samples\Functions\ImageMagick\Test;

use PHPUnit\Framework\TestCase;
use Google\Cloud\TestUtils\CloudFunctionLocalTestTrait;

require_once __DIR__ . '/TestCasesTrait.php';

/**
 * Class IntegrationTest.
 */
class IntegrationTest extends TestCase
{
    use CloudFunctionLocalTestTrait;
    use TestCasesTrait;

    /** @var string */
    private static $entryPoint = 'blurOffensiveImages';

    /** @var string */
    private static $functionSignatureType = 'cloudevent';

    /**
      * @dataProvider cases
      */
    public function testFunction(
        $cloudevent,
        $data,
        $label,
        $fileName,
        $expected,
        $statusCode
    ): void {
        // Prepare the HTTP headers for a CloudEvent.
        $cloudEventHeaders = [];
        foreach ($cloudevent as $key => $value) {
            $cloudEventHeaders['ce-' . $key] = $value;
        }

        // Send an HTTP request using CloudEvent metadata.
        $resp = $this->client->post('/', [
            'body' => json_encode($data),
            'headers' => $cloudEventHeaders + [
                // Instruct the function framework to parse the body as JSON.
                'content-type' => 'application/json'
            ],
        ]);

        // Confirm the status code.
        $this->assertEquals($statusCode, $resp->getStatusCode());

        // The Cloud Function logs all data to stderr.
        $actual = self::$localhost->getIncrementalErrorOutput();

        // Verify appropriate values are logged by the function.
        $this->assertContains($expected, $actual, $label . ':');
    }
}
