<?php

namespace App\Http\Controllers\Sandbox\GlobalFunctions;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class PHPExcelGenerator extends BaseGlobalFunction
{
    public function getName(): string
    {
        return 'PHPExcelGenerator';
    }

    public function getDescription(): string
    {
        return 'Excel 파일 생성 및 데이터 입력';
    }

    public function getParameters(): array
    {
        return [
            'data' => [
                'required' => true,
                'type' => 'array',
                'description' => 'Excel에 입력할 2차원 배열 데이터',
                'example' => [
                    ['이름', '나이', '이메일'],
                    ['홍길동', 25, 'hong@example.com'],
                    ['김영희', 30, 'kim@example.com']
                ]
            ],
            'filename' => [
                'required' => true,
                'type' => 'string',
                'description' => '생성할 파일명 (확장자 포함)',
                'example' => 'users.xlsx'
            ],
            'sheet_name' => [
                'required' => false,
                'type' => 'string',
                'description' => '시트명',
                'example' => 'User List'
            ],
            'has_headers' => [
                'required' => false,
                'type' => 'boolean',
                'description' => '첫 번째 행을 헤더로 처리할지 여부',
                'example' => true
            ],
            'auto_width' => [
                'required' => false,
                'type' => 'boolean',
                'description' => '열 너비 자동 조정 여부',
                'example' => true
            ]
        ];
    }

    public function execute(array $params): array
    {
        try {
            // 필수 파라미터 검증
            $this->validateParams($params, ['data', 'filename']);

            // 파라미터 추출
            $data = $params['data'];
            $filename = $params['filename'];
            $sheetName = $params['sheet_name'] ?? 'Sheet1';
            $hasHeaders = $params['has_headers'] ?? true;
            $autoWidth = $params['auto_width'] ?? true;

            // 데이터 유효성 검증
            if (!is_array($data) || empty($data)) {
                return $this->errorResponse('데이터가 비어있거나 올바른 배열이 아닙니다.');
            }

            // 첫 번째 행이 배열인지 확인 (2차원 배열 검증)
            if (!is_array($data[0])) {
                return $this->errorResponse('데이터는 2차원 배열이어야 합니다.');
            }

            // Spreadsheet 객체 생성
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // 시트명 설정
            $sheet->setTitle($sheetName);

            // 데이터 입력
            $rowNum = 1;
            foreach ($data as $rowData) {
                $colNum = 1;
                foreach ($rowData as $cellData) {
                    $sheet->setCellValueByColumnAndRow($colNum, $rowNum, $cellData);
                    $colNum++;
                }
                $rowNum++;
            }

            // 헤더 스타일 적용
            if ($hasHeaders && count($data) > 0) {
                $headerRange = 'A1:' . $sheet->getCell([count($data[0]), 1])->getCoordinate();
                
                // 헤더 스타일 설정
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 12
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => '4472C4']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ]);
            }

            // 데이터 영역 테두리 적용
            if (count($data) > 0) {
                $dataRange = 'A1:' . $sheet->getCell([count($data[0]), count($data)])->getCoordinate();
                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC']
                        ]
                    ]
                ]);
            }

            // 열 너비 자동 조정
            if ($autoWidth) {
                for ($col = 1; $col <= count($data[0]); $col++) {
                    $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
                }
            }

            // 저장 디렉토리 생성
            $exportDir = storage_path('app/sandbox-exports');
            if (!file_exists($exportDir)) {
                mkdir($exportDir, 0755, true);
            }

            // 파일명 생성 (타임스탬프 포함)
            $timestamp = now()->format('Y-m-d_H-i-s');
            $fileInfo = pathinfo($filename);
            $safeFilename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileInfo['filename']);
            $extension = $fileInfo['extension'] ?? 'xlsx';
            $finalFilename = $timestamp . '_' . $safeFilename . '.' . $extension;
            $filePath = $exportDir . '/' . $finalFilename;

            // Excel 파일 생성
            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);

            // 메모리 정리
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            // 파일 존재 확인
            if (!file_exists($filePath)) {
                return $this->errorResponse('파일 생성에 실패했습니다.');
            }

            // 성공 응답
            return $this->successResponse([
                'filename' => $finalFilename,
                'original_filename' => $filename,
                'file_size' => filesize($filePath),
                'rows_processed' => count($data),
                'columns_processed' => count($data[0])
            ], 'Excel 파일이 성공적으로 생성되었습니다.', [
                'file_path' => '/sandbox/download/' . $finalFilename,
                'download_url' => url('/sandbox/download/' . $finalFilename)
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse('Excel 파일 생성 중 오류가 발생했습니다: ' . $e->getMessage(), $e);
        }
    }
}