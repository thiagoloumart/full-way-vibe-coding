# Código do canônico `001-confirmacao-consultas`

> **Exemplo documental** — não é código executável dentro deste repositório da skill. É o código que a Fase 7 Implement **produziria** se o canônico fosse um projeto Laravel real. Foi redigido para ser **realista** (compilaria em Laravel 12 + PHP 8.3) e **exemplar** (segue as decisões documentadas nos artefatos anteriores), mas **nunca executado** neste ambiente.

## Layout

```
codigo/
  composer.json                 # stack D-001 materializada
  phpstan.neon · pint.json      # lint & análise estática (F10)
  .github/workflows/ci.yml      # CI (F10)
  config/
    services.php                # credenciais WhatsApp (F4)
  database/
    migrations/                 # 10 migrations (F1..F9)
  routes/
    web.php · webhooks.php      # rotas Laravel + webhooks (F4, F7)
  app/
    Domain/
      Cadastro/                 # scaffolding paciente + médico (F1)
      Agendamento/              # scaffolding consulta (F2)
      Confirmacao/              # núcleo do módulo — histórico imutável + jobs (F3, F5, F6, F7, F8)
      Notificacao/              # contrato abstrato (F4; Camada 1 D-E-02)
      Lgpd/                     # anonimização (F9)
    Infra/
      Notificacao/              # adaptadores concretos Meta/ZApi/Noop (F4)
    Http/
      Controllers/              # webhook + link público (F4, F7)
      Livewire/                 # UI (F1, F2, F7, F8, F9)
      Middleware/               # ExigeIsAdmin (F8)
    Policies/                   # autorização fina (F8; C-002)
    Models/                     # Eloquent models (F1..F4)
    Listeners/                  # listeners de evento (F3, F5)
    Logging/                    # PII masking processor (F10)
  tests/
    Contract/                   # NotificacaoDriver contract (F4)
    Feature/                    # feature tests (F1..F9)
    Unit/                       # unit tests (F3..F9)
    Integration/                # pipelines completos (F5, F10)
```

## Quais arquivos são **completos** vs **stubs**

Por estratégia **C2** acordada com o humano dono do canônico:

- **Arquivos completos** são os que demonstram as decisões arquiteturais do plan.md / constitution.md (~25 arquivos). Exemplos: `EventoConsulta.php` (append-only model), `NotificacaoDriver.php` (interface), `MetaCloudDriver.php` (retry + erro tipado), `AnonimizarPaciente.php` (transacional + lock pessimista), `DispararLembreteJob.php` (orquestração de 4 guards), `DerivarStatus.php` (reducer), trigger SQL de append-only.
- **Arquivos stub** são os CRUD triviais e UI simples (~35 arquivos). Têm namespace/classe/assinatura + docblock com referência à task e um `// TODO (T-NNN): implementar conforme tasks.md`.

Lista **completa** dos arquivos completos está em [IMPLEMENT_NOTES.md](../implement_notes.md).

## Como ler

Leia `app/Domain/Confirmacao/` primeiro. É onde está o coração do módulo (histórico imutável, derivação de status, job de disparo). Depois `app/Domain/Notificacao/Contracts/NotificacaoDriver.php` (interface abstrata) e `app/Infra/Notificacao/MetaCloudDriver.php` (implementação). Por último `app/Domain/Lgpd/Services/AnonimizarPaciente.php` que materializa a decisão C-003.

## Por que o código não é executado

Este repositório é a **skill** `full-way-vibe-coding`. O canônico é um **exemplo documental** da skill aplicada a D1 software, não um projeto Laravel operacional. O canônico vive dentro de `examples/`; o repositório da skill só tem Python do `harness` executável. Se você quiser rodar este código, copie `codigo/` para um projeto Laravel 12 greenfield e ajuste caminhos — mas isso é trabalho fora do escopo da skill.
