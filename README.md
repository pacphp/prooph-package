# prooph package for PAC

```yaml
prooph:
  company:
    event_store:
      connection: 'pdo_connection'
      event_bus: 'prooph_service_bus.company_event_bus'
      load_batch_size: 1000
      strategy: SingleStream
      type: mysql
      plugins: ~
      repositories:
        company_collection:
          repository_class: 'MyApp\Company\Repository\EventStore\CompanyCollection'
          aggregate_type: 'MyApp\Company\Aggregate\Company'

    command_bus:
      plugins: ~
      routes:
        MyApp\Company\Command\CreateCompany: 'prooph.handler.company_create'

    graphql:
      routes:
          MyApp\GraphQL\Field\CreateCompanyField: MyApp\Company\Command\CreateCompany

    projection:
      read_model_collection: 'prooph.read_model.company_collection'
      projection_class: MyApp\Company\Projection\CompanyProjector
      projectors:
          MyApp\Company\Event\CompanyWasCreated:
              - 'projector.company_created'

    event_bus:
      plugins:
        - 'prooph_service_bus.on_event_invoke_strategy'
      routes:
        'MyApp\Company\Event\CompanyWasCreated':
          - 'projector.company'
```
