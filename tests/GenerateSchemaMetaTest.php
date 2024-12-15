<?php

namespace Audience77\LaravelSchemaspyMeta\Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\TestCase;

class GenerateSchemaMetaTest extends TestCase
{
    private string $xmlFile;

    public function setUp(): void
    {
        parent::setUp();

        $randStr = Str::random(20);
        $this->xmlFile = "schemaspy-meta{$randStr}.xml";
    }

    public function tearDown(): void
    {
        parent::tearDown();
        if (file_exists($this->xmlFile)) {
            unlink(base_path($this->xmlFile));
        }
    }

    /**
     * 前提: リレーションがEloquent Modelクラスを継承したクラスに定義されている
     * 期値値: リレーションがXMLファイルに定義されている
     *
     * @dataProvider dataRelationship
     */
    public function testRelationship($input, $expected)
    {
        Artisan::call('generate:schemaspy-meta', [
            '--modelRootDir' => $this->getRelativePathFromProjectRoot($input['modelRootDir']),
            '--xmlFile' => $this->xmlFile,
        ]);

        $xml = simplexml_load_file($this->xmlFile);
        $this->assertXmlStructure($xml);
        foreach ($expected['relations'] as $r) {
            $this->assertRelationship($xml, $r['parent'], $r['child']);
        }
    }
    public function dataRelationship()
    {
        return [
            'HasMany' => [
                ['modelRootDir' => 'Models/HasMany'],
                ['relations' => [
                    ['parent' => 'planes.id', 'child' => 'flights.plane_id'],
                ]],
            ],
            'BelongsTo' => [
                ['modelRootDir' => 'Models/BelongsTo'],
                ['relations' => [
                    ['parent' => 'planes.id', 'child' => 'flights.plane_id'],
                ]],
            ],
            'HasOne' => [
                ['modelRootDir' => 'Models/HasOne'],
                ['relations' => [
                    ['parent' => 'planes.id', 'child' => 'certifications.plane_id'],
                ]],
            ],
            'BelongsToMany' => [
                ['modelRootDir' => 'Models/BelongsToMany'],
                ['relations' => [
                    ['parent' => 'flights.id', 'child' => 'flight_pilot.flight_id'],
                    ['parent' => 'pilots.id', 'child' => 'flight_pilot.pilot_id'],
                ]],
            ],
            'MorphOne' => [
                ['modelRootDir' => 'Models/MorphOne'],
                ['relations' => [
                    ['parent' => 'pilots.id', 'child' => 'contacts.contactable_id'],
                    ['parent' => 'crews.id', 'child' => 'contacts.contactable_id'],
                ]],
            ],
            'MorphMany' => [
                ['modelRootDir' => 'Models/MorphMany'],
                ['relations' => [
                    ['parent' => 'pilots.id', 'child' => 'boarding_logs.boarding_loggable_id'],
                    ['parent' => 'crews.id', 'child' => 'boarding_logs.boarding_loggable_id'],
                ]],
            ],
            'MorphToMany' => [
                ['modelRootDir' => 'Models/MorphToMany'],
                ['relations' => [
                    ['parent' => 'passengers.id', 'child' => 'flightables.flightable_id'],
                    ['parent' => 'cargos.id', 'child' => 'flightables.flightable_id'],
                    ['parent' => 'flights.id', 'child' => 'flightables.flight_id'],
                ]],
            ],
            'MorphedByMany' => [
                ['modelRootDir' => 'Models/MorphedByMany'],
                ['relations' => [
                    ['parent' => 'passengers.id', 'child' => 'flightables.flightable_id'],
                    ['parent' => 'cargos.id', 'child' => 'flightables.flightable_id'],
                    ['parent' => 'flights.id', 'child' => 'flightables.flight_id'],
                ]],
            ],
            'DB has multiple tables' => [
                ['modelRootDir' => 'Models/MultipleTables'],
                ['relations' => [
                    ['parent' => 'planes.id', 'child' => 'flights.plane_id'],
                    ['parent' => 'planes.id', 'child' => 'certifications.plane_id'],
                ]],
            ],
            'Table has multiple foreign keys' => [
                ['modelRootDir' => 'Models/MultipleForeignKeys'],
                ['relations' => [
                    ['parent' => 'planes.id', 'child' => 'flights.plane_id'],
                    ['parent' => 'air_routes.id', 'child' => 'flights.air_route_id'],
                ]],
            ],
            'Both models (parent and child) define relationship' => [
                ['modelRootDir' => 'Models/BothDirectionsRelationship'],
                ['relations' => [
                    ['parent' => 'planes.id', 'child' => 'flights.plane_id'],
                ]],
            ],
            'Model exists in subdirectory' => [
                ['modelRootDir' => 'Models/Subdirectory'],
                ['relations' => [
                    ['parent' => 'planes.id', 'child' => 'flights.plane_id'],
                ]],
            ],
            'Indirectly inherit Eloquent Model' => [
                ['modelRootDir' => 'Models/AbstractModel'],
                ['relations' => [
                    ['parent' => 'planes.id', 'child' => 'flights.plane_id'],
                ]],
            ],
        ];
    }

    /**
     * 前提: --excludeModelFilesでファイルを指定する
     * 期値値: 指定されたファイルが処理されない
     */
    public function testExcludeModel()
    {
        Artisan::call('generate:schemaspy-meta', [
            '--modelRootDir' => $this->getRelativePathFromProjectRoot('Models/HasMany'),
            '--excludeModelFiles' => [
                $this->getRelativePathFromProjectRoot('Models/HasMany/Plane.php'),
                $this->getRelativePathFromProjectRoot('Models/HasMany/Flight.php'),
            ],
            '--xmlFile' => $this->xmlFile,
        ]);

        $this->assertFileDoesNotExist($this->xmlFile);
        $output = Artisan::output();
        $this->assertStringContainsString('Not found PHP files under the specified root directory.', $output);
    }

    /**
     * 前提: --modelRootDirにPHPファイルが存在しない
     * 期値値: XMLファイルが生成されない
     */
    public function testNoPhpFile()
    {
        Artisan::call('generate:schemaspy-meta', [
            '--modelRootDir' => $this->getRelativePathFromProjectRoot('Models/NoPhpFile'),
            '--xmlFile' => $this->xmlFile,
        ]);

        $this->assertFileDoesNotExist($this->xmlFile);
        $output = Artisan::output();
        $this->assertStringContainsString('Not found PHP files under the specified root directory.', $output);
    }

    /**
     * 前提: --modelRootDirにEloquent Modelファイルが存在しない
     * 期値値: XMLファイルが生成されない
     *
     * @dataProvider dataNoModelFile
     */
    public function testNoModelFile($input)
    {
        Artisan::call('generate:schemaspy-meta', [
            '--modelRootDir' => $this->getRelativePathFromProjectRoot($input['modelRootDir']),
            '--xmlFile' => $this->xmlFile,
        ]);

        $this->assertFileDoesNotExist($this->xmlFile);
        $output = Artisan::output();
        $this->assertStringContainsString('Not found Eloquent Model files under the specified root directory.', $output);
    }
    public function dataNoModelFile()
    {
        return [
            'Class is not extended by Eloquent Model' => [
                ['modelRootDir' => 'Models/NotExtendsModel'],
            ],
            'Class is not exist in file' => [
                ['modelRootDir' => 'Models/NoClass'],
            ],
            'Namespace is not exist in file' => [
                ['modelRootDir' => 'Models/NoNamespace'],
            ],
            'File is empty' => [
                ['modelRootDir' => 'Models/EmptyFile'],
            ],
        ];
    }

    /**
     * プロジェクトルートからの相対パスを取得する
     * @param string $relativePathFromThisFile このファイルからの相対パス
     * @return string プロジェクトルートからの相対パス
     */
    private function getRelativePathFromProjectRoot($relativePathFromThisFile)
    {
        $fullPath = __DIR__ . '/' . $relativePathFromThisFile;
        return str_replace(base_path() . '/', '', $fullPath);
    }

    /**
     * 以下の条件を満たすことを確認する
     * - xmlファイルが正しく読み込まれている
     * - schemaMetaタグがトップレベルに存在する
     * - tablesタグがschemaMetaタグの直下に1つのみ存在する
     * - tableタグがtablesタグの直下に複数存在し、name属性が重複していない
     * - columnタグがtableタグの直下に複数存在し、name属性が重複していない
     * - foreignKeyタグがcolumnタグの直下に複数存在し、table属性とcolumn属性が重複していない
     */
    private function assertXmlStructure($xml)
    {
        $this->assertNotFalse($xml, 'Failed to load xml file.');
        $this->assertEquals('schemaMeta', $xml->getName(), 'Top level tag is not schemaMeta.');

        $tablesTags = $xml->tables;
        $this->assertEquals(1, count($tablesTags), "Multiple tables tag exist in schemaMeta tag.");
        $tablesTag = $tablesTags[0];
        $tableNames = [];
        foreach ($tablesTag->table as $tableTag) {
            $nameAttribute = (string) $tableTag['name'];
            $this->assertNotContains($nameAttribute, $tableNames, "Table $nameAttribute is duplicated.");
            $tableNames[] = $nameAttribute;
            $columnNames = [];
            foreach ($tableTag->column as $columnTag) {
                $nameAttribute = (string) $columnTag['name'];
                $this->assertNotContains($nameAttribute, $columnNames, "Column $nameAttribute is duplicated.");
                $columnNames[] = $nameAttribute;
                $foreignKeyNames = [];
                foreach ($columnTag->foreignKey as $foreignKeyTag) {
                    $tableAttribute = (string) $foreignKeyTag['table'];
                    $columnAttribute = (string) $foreignKeyTag['column'];
                    $foreignKeyName = "$tableAttribute.$columnAttribute";
                    $this->assertNotContains($foreignKeyName, $foreignKeyNames, "ForeignKey $foreignKeyName is duplicated.");
                    $foreignKeyNames[] = $foreignKeyName;
                }
            }
        }
    }

    /**
     * 指定されたリレーションシップがxmlに存在することを確認する
     */
    private function assertRelationship($xml, $parent, $child)
    {
        [$parentTable, $ownerKey] = explode('.', $parent);
        [$childTable, $foreignKey] = explode('.', $child);

        $tablesTags = $xml->tables;
        $tablesTag = $tablesTags[0];
        $foreignKeyExists = false;
        foreach ($tablesTag->table as $tableTag) {
            $nameAttribute = (string) $tableTag['name'];
            if ($nameAttribute !== $childTable) {
                continue;
            }
            foreach ($tableTag->column as $columnTag) {
                $nameAttribute = (string) $columnTag['name'];
                if ($nameAttribute !== $foreignKey) {
                    continue;
                }
                foreach ($columnTag->foreignKey as $foreignKeyTag) {
                    $tableAttribute = (string) $foreignKeyTag['table'];
                    $columnAttribute = (string) $foreignKeyTag['column'];
                    if ($tableAttribute === $parentTable && $columnAttribute === $ownerKey) {
                        $foreignKeyExists = true;
                        break 3;
                    }
                }
            }
        }
        $this->assertTrue($foreignKeyExists, "Foreign key $foreignKey does not exist in table $childTable.");
    }
}
