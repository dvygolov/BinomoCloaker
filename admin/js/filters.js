var tdsFilters = [
    {
        id: 'os',
        label: 'OS',
        input: 'text',
        type: 'string',
        operators: ['in', 'not_in'],
        placeholder: 'Android,iOS,Windows,OS X',
        size: 50
    },
    {
        id: 'osver',
        label: 'OS version',
        input: 'number',
        type: 'integer',
        operators: ['equal', 'not_equal','less_or_equal','greater_or_equal'],
        placeholder: 10,
        size: 50
    },
    {
        id: 'device',
        label: 'Device',
        input: 'text',
        type: 'string',
        operators: ['in', 'not_in'],
        placeholder: 'desktop,mobile',
        size: 100
    },
    {
        id: 'brand',
        label: 'Brand',
        input: 'text',
        type: 'string',
        operators: ['contains', 'not_contains'],
        size: 100
    },
    {
        id: 'model',
        label: 'Model',
        input: 'text',
        type: 'string',
        operators: ['contains', 'not_contains'],
        size: 100
    },
    {
        id: 'client',
        label: 'Client',
        input: 'text',
        type: 'string',
        operators: ['contains', 'not_contains'],
        size: 100
    },
    {
        id: 'clientver',
        label: 'ClientVer',
        input: 'text',
        type: 'string',
        operators: ['contains', 'not_contains'],
        size: 100
    },
    {
        id: 'country',
        label: 'Country',
        input: 'text',
        type: 'string',
        operators: ['in', 'not_in'],
        placeholder: 'RU,BY,UA'

    },
    {
        id: 'language',
        label: 'Language',
        input: 'text',
        type: 'string',
        operators: ['in', 'not_in'],
        placeholder: 'en,ru'
    },
    {
        id: 'url',
        label: 'URL',
        input: 'text',
        type: 'string',
        operators: ['contains', 'not_contains'],
        size: 100
    },
    {
        id: 'useragent',
        label: 'UserAgent',
        input: 'text',
        type: 'string',
        operators: ['contains', 'not_contains'],
        size: 100,
        placeholder: 'facebook,facebot,curl,gce-spider,yandex.com,odklbot'
    },
    {
        id: 'isp',
        label: 'ISP',
        input: 'text',
        type: 'string',
        operators: ['contains', 'not_contains'],
        size: 100,
        placeholder: 'facebook,google,yandex,amazon,azure,digitalocean,microsoft'
    },
    {
        id: 'referer',
        label: 'Referer',
        input: 'text',
        type: 'string',
        operators: ['not_equal', 'contains', 'not_contains'],
        validation: {
            allow_empty_value: true
        },
        size: 100
    },
    {
        id: 'vpntor',
        label: 'VPN&Tor',
        type: 'integer',
        input: 'radio',
        values: {
            0: 'Detected',
            1: 'NOT Detected'
        },
        operators: ['equal']
    },
    {
        id: 'ipbase',
        label: 'IP Base',
        type: 'string',
        operators: ['contains', 'not_contains'],
        placeholder: 'path to base file(s) in bases folder: bots.txt',
        size: 100
    }
];