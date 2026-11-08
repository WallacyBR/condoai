<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Bank;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use SimpleXMLElement;

class BankStatementService
{
    /**
     * Processa um arquivo OFX ou CSV e cria transações no sistema.
     */
    public function processUpload(\Illuminate\Http\UploadedFile $file, Bank $bank): Collection
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $content = $file->get();
        $entries = $extension === 'ofx'
            ? $this->parseOfx($content)
            : $this->parseCsv($content);

        $transactions = collect();
        foreach ($entries as $entry) {
            $transactions->push(Transaction::create([
                'chart_account_id' => $entry['chart_account_id'] ?? null,
                'condominium_id' => $bank->condominium_id,
                'created_by' => auth()->id(),
                'date' => $entry['date'],
                'amount' => $entry['amount'],
                // Valores positivos representam receitas, negativos, despesas
                'type' => $entry['amount'] >= 0
                    ? TransactionType::RECEITA
                    : TransactionType::DESPESA,
                'status' => 'pendente',
                'description' => $entry['description'],
            ]));
        }

        return $transactions;
    }

    private function parseOfx(string $content): array
    {
        $xml = new SimpleXMLElement($content, LIBXML_NOCDATA);
        $rows = [];
        foreach ($xml->BANKMSGSRSV1->STMTTRNRS->STMTRS->BANKTRANLIST->STMTTRN as $t) {
            $rows[] = [
                'date' => (string) $t->DTPOSTED,
                'amount' => (float) $t->TRNAMT,
                'description' => (string) $t->MEMO,
            ];
        }

        return $rows;
    }

    private function parseCsv(string $content): array
    {
        $rows = [];
        foreach (array_filter(array_map('trim', explode("\n", $content))) as $line) {
            $cols = str_getcsv($line, ';');
            if (count($cols) < 3) {
                continue;
            }
            $rows[] = [
                'date' => $cols[0],
                'amount' => (float) str_replace(',', '.', $cols[1]),
                'description' => $cols[2],
            ];
        }

        return $rows;
    }
}
