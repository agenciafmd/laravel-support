# Laravel – Support

[![Downloads](https://img.shields.io/packagist/dt/agenciafmd/laravel-support.svg?style=flat-square)](https://packagist.org/packages/agenciafmd/laravel-support)
[![Licença](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

Itens úteis compartilhados entre os projetos da Agência F&MD: helpers de formatação e sanitização (telefone, CPF, CNPJ, CEP, YouTube, moeda...), regras de validação, macros de `Str`, `Request` e Eloquent, diretiva Blade de cache por fragmento, cast de arquivos, traits para formulários e seeders e um provider extra para o Faker.

## Requisitos

- Laravel ^12.0 | ^13.0
- geekcom/validator-docs ^3.12
- spatie/image-optimizer ^1.8
- ext-bcmath

## Instalação

```bash
composer require agenciafmd/laravel-support:dev-master
```

Caso esteja desenvolvendo localmente dentro de um monorepo, adicione o repositório `path` no `composer.json` do app e rode `composer require agenciafmd/laravel-support:*`.

O service provider e o alias `Helper` são registrados automaticamente (package discovery). Não há arquivo de configuração nem `.env`.

## Uso

### Helper

`Agenciafmd\Support\Helper` (também disponível pelo alias `Helper`) reúne métodos estáticos. Os `sanitize*` retornam `null` quando o valor é inválido.

```php
use Agenciafmd\Support\Helper;

Helper::sanitizePhone('17999999999');    // (17) 99999-9999
Helper::sanitizeCpf('12345678909');      // 123.456.789-09 (validado com a regra `cpf`)
Helper::sanitizePostalCode('15015000');  // 15015-000
Helper::mask('12345678', '#####-###');   // 12345-678
Helper::formatMoney(123456);             // R$ 1.234,56
Helper::youtubeId('https://youtu.be/dQw4w9WgXcQ'); // dQw4w9WgXcQ
```

| Método | Descrição |
|---|---|
| `onlyNumbers($string)` / `onlyAlphanumeric($string)` | Remove tudo que não for número / letra ou número |
| `mask($string, $mask)` | Aplica a máscara; cada `#` recebe o próximo caractere |
| `secretPhone($phone, $initialChars = 4, $finalChars = 2)` | Ofusca o telefone com `*`, mantendo o início e o fim |
| `sanitizePhone($phone)` | Formata telefones com 10 ou 11 dígitos; outros tamanhos retornam `null` |
| `sanitizeCpf($cpf)` / `sanitizeCnpj($cnpj)` | Valida (`geekcom/validator-docs`) e formata CPF / CNPJ |
| `sanitizeRg($rg)` | Normaliza e formata o RG |
| `sanitizeEmail($email)` | Valida e retorna o e-mail |
| `sanitizeName($name)` | Normaliza nomes (`JOÃO DA SILVA` → `João da Silva`) |
| `sanitizePostalCode($cep)` | Formata o CEP (`#####-###`) |
| `sanitizeSchedule($time)` | Valida e formata um horário `HH:MM` |
| `sanitizeYoutube($url)` / `youtubeId($url)` | Normaliza o link do YouTube para o formato de compartilhar / retorna o id do vídeo |
| `printable($string)` | Mantém somente caracteres imprimíveis |
| `success($data, $message, $code)` / `error($data, $message, $code)` | Resposta JSON normalizada (`code`, `message`, `data`) |
| `formatMoney($value, $currency = 'R$ ')` | Formata um inteiro (centavos) como moeda |
| `floatToInt($value)` | Converte valor decimal para centavos (via `bcmath`) |
| `httpStripQueryParam($param, $value = null, $url = null)` | Remove um parâmetro (ou um valor dele) da query string |
| `numbersToWords($value)` | Troca os dígitos da string por palavras (`1` → `um`) |
| `contrastColor($hex, $dark, $light)` / `hexToRgb($hex)` | Cor de texto com contraste para o fundo / converte hex para RGB |
| `statesCities()` / `states()` / `cities($uf)` | Estados e cidades lidos de `public/json/estados-cidades.json` (em cache) |
| `getContentAndExtensionFromBase64File($string)` | Retorna `[conteúdo binário, extensão]` de um arquivo em base64 |
| `aspectRatio($width, $height)` | Proporção simplificada (`1920, 1080` → `16:9`) |

> `statesCities()`, `states()` e `cities()` dependem do arquivo `public/json/estados-cidades.json` na aplicação.

### Regras de validação

```php
use Agenciafmd\Support\Rules\CommaSeparatedEmails;
use Agenciafmd\Support\Rules\HumanName;
use Agenciafmd\Support\Rules\YouTubeUrl;

$request->validate([
    'name' => ['required', new HumanName()],
    'emails' => ['required', new CommaSeparatedEmails()],
    'video' => ['nullable', new YouTubeUrl()],
]);
```

| Regra | Descrição |
|---|---|
| `CommaSeparatedEmails` | Lista de e-mails separados por vírgula, ponto e vírgula ou espaço; cada um validado com `email:rfc,dns` |
| `HumanName` | Barra nomes com cara de bot/hash (5+ consoantes seguidas, alternância de maiúsculas/minúsculas, palavras longas com poucas vogais) |
| `YouTubeUrl` | Aceita somente links do YouTube reconhecidos pelo `Helper::sanitizeYoutube()` |

As regras `cpf`, `cnpj` etc. vêm do `geekcom/validator-docs`, instalado como dependência.

### Macros de `Str` / `Stringable`

```php
use Illuminate\Support\Str;

Str::acronym('Agência F&MD');             // AFM
Str::readDuration($article->content);     // minutos de leitura (200 palavras/min, mínimo 1)
Str::localSquish("  texto \u{3164} aqui "); // remove também espaços "invisíveis"
Str::printable($string);
Str::numbersToWords('Apto 12');           // Apto umdois

str('JOÃO DA SILVA')->sanitizeName()->toString();
```

Disponíveis em `Str`: `acronym`, `readDuration`, `localSquish`, `printable`, `numbersToWords`.
Disponíveis em `Stringable` (`str()`): `acronym`, `readDuration`, `sanitizeName`, `localSquish`, `printable`, `numbersToWords`.

### Macro de `Request`

```php
request()->currentRouteNameStartsWith('frontend.articles');
request()->currentRouteNameStartsWith(['frontend.articles', 'frontend.categories']);
```

### Macros do Eloquent Builder

Monta as opções de um select com `label` / `value` (já com a opção vazia `-` no início):

```php
Category::query()->toSelectOptions();                      // label: name, value: id
Category::query()->toSelectOptions('title', 'id', disabled: true); // marca como disabled os itens com is_active = false

Category::query()->toSimpleSelectOptions('title', 'id');   // ['' => '-', 1 => 'Título', ...]
```

### Diretiva Blade `@cache`

Cache de fragmentos de view (estilo "matryoshka"). A chave pode ser uma string, uma `Collection` ou um model com `getCacheKey()`:

```php
public function getCacheKey(): string
{
    return sprintf('%s/%s-%s', static::class, $this->getKey(), $this->updated_at->timestamp);
}
```

```blade
@cache($article)
    <article>...</article>
@endcache
```

> O cache usa **tags** (`views`), então o driver precisa suportá-las (ex.: Redis). Em ambiente `local` com `CACHE_STORE=redis`, a tag `views` é limpa a cada requisição HTTP.

### Cast `Files`

Normaliza um repeater de arquivos (`name`, `file`) e grava o tamanho em bytes (`size`) de cada item no momento do save. Itens sem `file` são descartados.

```php
use Agenciafmd\Support\Casts\Files;

protected function casts(): array
{
    return [
        'files' => Files::class,
    ];
}
```

### Traits

- `Agenciafmd\Support\Traits\FormRateLimiter` — `withRateLimiter()` limita a 5 tentativas por classe + IP e lança `ValidationException` no campo `email` (usado nos formulários Livewire).
- `Agenciafmd\Support\Traits\SeederSupport` — apoio a seeders de migração de legado: lê `database/data/{arquivo}.json` (`rows()`), converte valores (`string()`, `integer()`, `boolean()`) e baixa a mídia do site antigo para o storage (`putOnStorage()`). Implemente `attributes()` e sobrescreva `storageUrl()` com a URL da mídia do legado.

### Faker

Métodos extras disponíveis em `fake()` / `$this->faker`:

| Método | Descrição |
|---|---|
| `youtubeId()`, `youtubeUri()`, `youtubeShortUri()`, `youtubeEmbedUri()`, `youtubeEmbedCode()`, `youtubeChannelUri()`, `youtubeRandomUri()` | Ids, links e embeds do YouTube |
| `localImage($ratio = '16x9', $sourceDir = null)` | Imagem local do pacote (`1x1`, `3x2`, `3x4`, `4x3`, `9x16`, `16x9`, `21x9`) |
| `localFile($sourceDir = null)` | PDF local do pacote |
| `tags($max = 3, $allowed = [])` | Lista aleatória de tags |
| `htmlParagraph()`, `htmlParagraphs()`, `htmlText()` | Conteúdo HTML para editores ricos |
| `icon()` | Nome de um ícone Heroicons (`c-*`) |

A extensão `extension.neon` expõe esses métodos para o PHPStan (incluída automaticamente com `phpstan/extension-installer`).

## Testes

Os testes ficam em `tests/` e usam o `Tests\TestCase` da aplicação, então são executados de dentro do projeto que instala o pacote:

```bash
vendor/bin/pest packages/agenciafmd/laravel-support/tests
```

## Licença

Este pacote é software livre e está disponível nos termos da licença MIT.
