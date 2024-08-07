<?php

// Dados de autenticação
$email = "Login Portal";
$password = "senha Portal";

// Endpoint da API para autenticação
$url_auth = 'https://api.movingpay.com.br/api/v3/acessar';

// Dados para enviar no corpo da requisição de autenticação
$data_auth = array(
    'email' => $email,
    'password' => $password
);

// Configuração da requisição de autenticação
$ch_auth = curl_init($url_auth);
curl_setopt($ch_auth, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_auth, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch_auth, CURLOPT_POST, true);
curl_setopt($ch_auth, CURLOPT_POSTFIELDS, json_encode($data_auth));

// Executa a requisição de autenticação
$response_auth = curl_exec($ch_auth);

// Verifica se houve erro na execução do cURL
if ($response_auth === false) {
    echo 'Erro cURL na autenticação: ' . curl_error($ch_auth) . "\n";
    exit; // Encerra o script se houver erro
}

// Decodifica a resposta JSON da autenticação
$response_data_auth = json_decode($response_auth, true);

// Verifica a resposta da API para a autenticação
if ($response_data_auth && isset($response_data_auth['access_token'])) {
    $access_token = $response_data_auth['access_token'];
    echo "Token de acesso obtido: " . $access_token . "\n";

    // Define o período do mês atual
    $start_date = date('Y-m-01'); // Primeiro dia do mês
    $finish_date = date('Y-m-t'); // Último dia do mês
    $customer_id = 135; // Substitua pelo ID correto do cliente

    // Endpoint da API para buscar transações
    $url_transacoes = 'https://api.movingpay.com.br/api/v3/transacoes';

    // Adiciona parâmetros à URL
    $url_transacoes .= '?start_date=' . urlencode($start_date) .
                       '&finish_date=' . urlencode($finish_date) .
                       '&customer=' . urlencode($customer_id);

    // Configuração da requisição de busca de transações
    $ch_transacoes = curl_init($url_transacoes);
    curl_setopt($ch_transacoes, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch_transacoes, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token // Token de autenticação
    ));
    curl_setopt($ch_transacoes, CURLOPT_HTTPGET, true); // Muda para método GET

    // Executa a requisição de busca de transações
    $response_transacoes = curl_exec($ch_transacoes);

    // Verifica se houve erro na execução do cURL
    if ($response_transacoes === false) {
        echo 'Erro cURL na busca de transações: ' . curl_error($ch_transacoes) . "\n";
    } else {
        // Decodifica a resposta JSON da busca de transações
        $response_data_transacoes = json_decode($response_transacoes, true);

        // Verifica a resposta da API para a busca de transações
        if ($response_data_transacoes) {
            echo "Resposta da API para transações:\n";
            print_r($response_data_transacoes); // Exibe a resposta completa para depuração

            if (isset($response_data_transacoes['transacoes'])) {
                $transacoes = $response_data_transacoes['transacoes']; // Ajuste conforme a estrutura da resposta

                // Contagem das transações por situação
                $contagem_situacoes = array(
                    'APPR' => 0,
                    'DECL' => 0,
                    'REFD' => 0
                );

                foreach ($transacoes as $transacao) {
                    if (isset($transacao['situacao']) && array_key_exists($transacao['situacao'], $contagem_situacoes)) {
                        $contagem_situacoes[$transacao['situacao']]++;
                    }
                }

                // Mostra contagem (ou processa para visualização)
                echo "Contagem de transações:\n";
                print_r($contagem_situacoes);

                // Cria um gráfico (ou exporta para um formato que possa ser usado em gráficos)
                $json_data = json_encode($contagem_situacoes);
                
                // Verifica se a codificação JSON foi bem-sucedida
                if ($json_data === false) {
                    echo "Erro na codificação JSON: " . json_last_error_msg() . "\n";
                } else {
                    // Salva os dados em um arquivo JSON
                    $file_path = 'contagem_transacoes.json';
                    $result = file_put_contents($file_path, $json_data);
                    
                    // Verifica se a escrita no arquivo foi bem-sucedida
                    if ($result === false) {
                        echo "Erro ao criar o arquivo JSON.\n";
                    } else {
                        echo "Arquivo JSON criado com sucesso.\n";
                    }
                }
            } else {
                echo "Nenhuma transação encontrada ou a estrutura da resposta é diferente.\n";
            }
        } else {
            echo "Erro ao processar a resposta da API.\n";
        }
    }

    // Fecha a conexão de busca de transações
    curl_close($ch_transacoes);

} else {
    echo "Erro ao obter token de acesso.\n";
    if (isset($response_data_auth)) {
        print_r($response_data_auth); // Exibe a resposta completa para depuração
    }
}

// Fecha a conexão de autenticação
curl_close($ch_auth);

?>
