# auxmoney OpentracingBundle - Zipkin

[![test](https://github.com/auxmoney/OpentracingBundle-Zipkin/workflows/test/badge.svg)](https://github.com/auxmoney/OpentracingBundle-Zipkin/actions?query=workflow%3Atest)
![GitHub release (latest SemVer)](https://img.shields.io/github/v/release/auxmoney/OpentracingBundle-Zipkin)
![Coveralls github](https://img.shields.io/coveralls/github/auxmoney/OpentracingBundle-Zipkin)
![Codacy Badge](https://api.codacy.com/project/badge/Grade/626c5a0a955b4318bb9a4f82bd2ee7a2)
![Code Climate maintainability](https://img.shields.io/codeclimate/maintainability/auxmoney/OpentracingBundle-Zipkin)
![Scrutinizer code quality (GitHub/Bitbucket)](https://img.shields.io/scrutinizer/quality/g/auxmoney/OpentracingBundle-Zipkin)
![GitHub](https://img.shields.io/github/license/auxmoney/OpentracingBundle-Zipkin)

This symfony bundle provides a tracer implementation for [Zipkin](https://zipkin.io/) for the [auxmoney OpentracingBundle](https://github.com/auxmoney/OpentracingBundle-core).

Please have a look at [the central documentation](https://github.com/auxmoney/OpentracingBundle-core) for installation and usage instructions.

## Configuration

You can optionally configure environment variables, however, the default configuration will sample every request.
If you cannot change environment variables in your project, you can alternatively overwrite the container parameters directly.

| environment variable | container parameter | type | default | description |
|---|---|---|---|---|
| AUXMONEY_OPENTRACING_SAMPLER_CLASS | auxmoney_opentracing.sampler.class | `string` | `Zipkin\Samplers\BinarySampler` | class of the using sampler, see [existing samplers](#existing-samplers) |
| AUXMONEY_OPENTRACING_SAMPLER_VALUE | auxmoney_opentracing.sampler.value | `string` | `'true'` | must be a JSON decodable string, for the configuration of the sampler |

### Existing Samplers

* constant sampler
    * Class: `Zipkin\Samplers\BinarySampler` 
    * possible values: `'true'` / `'false'`
    * Description: you activate or deactivate the tracing

* percentage sampler
    * Class: `Zipkin\Samplers\PercentageSampler` 
    * possible values: Rate min `'0.00'` - max `'1.00'`
    * Description: you activate the tracing for the given rate
