import {Controller, useFormContext} from "react-hook-form";

import {DatePickerInput} from "@/components/ui/DatePickerInput";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import {Input} from "@/components/ui/input";
import {Label} from "@/components/ui/label";
import {TabsContent} from "@/components/ui/tabs";
import {FormValues} from "../types";
import {DocumentsList} from "../DocumentsList";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import {Button} from "@/components/ui/button";
import {Trash2} from "lucide-react";

import type {ReferenceData} from "@/types/reference";

interface IntroductionTabProps {
    openDocument: (document: { id: number; name: string }) => void;
    onDownload: (document: { id: number; name: string }) => void;
    referenceData?: ReferenceData;
    contractData?: Record<string, unknown> | null;
    onNavigateToField?: (fieldInfo: { tab: string; accordion?: string; fieldId: string }) => void;
}

export const IntroductionTab = ({
                                    openDocument,
                                    onDownload,
                                    referenceData,
                                    contractData,
                                    onNavigateToField,
                                }: IntroductionTabProps): JSX.Element => {
    const {register, control, watch} = useFormContext<FormValues>();

    const parseDateTime = (value?: string) => {
        if (!value) return {date: "", time: ""};
        const [date, timeWithZone] = value.split("T");
        const time = timeWithZone?.slice(0, 5) ?? "";
        return {date, time};
    };

    const combineDateTime = (date: string, time: string) => {
        if (!date && !time) return "";
        if (!date) return time ? `T${time}` : "";
        return time ? `${date}T${time}` : date;
    };
    const toIdString = (value: unknown) => {
        if (typeof value === "string" || typeof value === "number") {
            return String(value);
        }
        if (value && typeof value === "object" && "id" in (value as any)) {
            return String((value as any).id);
        }
        return "";
    };

    const documents =
        (
            contractData?.judicial_procedure_initiation as {
                documents?: Array<{ id: number; name: string }>;
            }
        )?.documents || [];

    return (
        <TabsContent value="judicial_procedure_initiation" className="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Введение процедуры</CardTitle>
                    <CardDescription>
                        Данные для введения процедуры банкротства
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div className="space-y-2">
                            <Controller
                                name="judicial_procedure_initiation.procedureInitiationDecisionDate"
                                control={control}
                                render={({field}) => (
                                    <DatePickerInput
                                        id="judicial_procedure_initiation.procedureInitiationDecisionDate"
                                        name="judicial_procedure_initiation.procedureInitiationDecisionDate"
                                        label="Дата принятия судебного решения"
                                        value={(field.value as string) ?? ""}
                                        onChange={field.onChange}
                                    />
                                )}
                            />
                        </div>
                        <div className="space-y-2">
                            <Controller
                                name="judicial_procedure_initiation.procedureInitiationResolutionDate"
                                control={control}
                                render={({field}) => (
                                    <DatePickerInput
                                        id="judicial_procedure_initiation.procedureInitiationResolutionDate"
                                        name="judicial_procedure_initiation.procedureInitiationResolutionDate"
                                        label="Дата объявления резолютивной части судебного решения"
                                        value={(field.value as string) ?? ""}
                                        onChange={field.onChange}
                                    />
                                )}
                            />
                        </div>

                        <Controller
                            name="judicial_procedure_initiation.procedureInitiationReportHearingDateTime"
                            control={control}
                            render={({ field }) => {
                                const { date, time } = parseDateTime(field.value as string);
                                return (
                                    <div className="space-y-2">
                                        <Label htmlFor="judicial_procedure_initiation.procedureInitiationReportHearingDateTime">
                                            Информация о дате и времени рассмотрения отчёта
                                        </Label>
                                        <div className="grid grid-cols-2 gap-2">
                                            <DatePickerInput
                                                id="judicial_procedure_initiation.procedureInitiationReportHearingDateTime"
                                                name="judicial_procedure_initiation.procedureInitiationReportHearingDateTime"
                                                value={date}
                                                onChange={(nextDate) =>
                                                    field.onChange(
                                                        combineDateTime(
                                                            typeof nextDate === "string"
                                                                ? nextDate
                                                                : "",
                                                            time
                                                        )
                                                    )
                                                }
                                                className="space-y-1"
                                            />
                                            <div className="space-y-1">
                                                <Input
                                                    id="judicial_procedure_initiation.procedureInitiationReportHearingDateTime_time"
                                                    value={time}
                                                    placeholder="15:30"
                                                    onChange={(e) =>
                                                        field.onChange(
                                                            combineDateTime(date, e.target.value)
                                                        )
                                                    }
                                                />
                                            </div>
                                        </div>
                                    </div>
                                );
                            }}
                        />

                        <Controller
                            name="judicial_procedure_initiation.procedureInitiationMchs"
                            control={control}
                            render={({field}) => (
                                <div className="space-y-2">
                                    <Label htmlFor="judicial_procedure_initiation.procedureInitiationMchs">
                                        ГИМС
                                    </Label>
                                    <Select
                                        value={toIdString(field.value)}
                                        onValueChange={field.onChange}
                                    >
                                        <SelectTrigger id="judicial_procedure_initiation.procedureInitiationMchs">
                                            <SelectValue placeholder="Выберите ГИМС"/>
                                        </SelectTrigger>
                                        <SelectContent>
                                            {referenceData?.mchs?.map((item) => (
                                                <SelectItem key={item.id} value={String(item.id)}>
                                                    {item.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                            )}
                        />

                        <Controller
                            name="judicial_procedure_initiation.procedureInitiationGostekhnadzor"
                            control={control}
                            render={({field}) => (
                                <div className="space-y-2">
                                    <Label htmlFor="judicial_procedure_initiation.procedureInitiationGostekhnadzor">
                                        Гостехнадзор
                                    </Label>
                                    <Select
                                        value={toIdString(field.value)}
                                        onValueChange={field.onChange}
                                    >
                                        <SelectTrigger
                                            id="judicial_procedure_initiation.procedureInitiationGostekhnadzor">
                                            <SelectValue placeholder="Выберите Гостехнадзор"/>
                                        </SelectTrigger>
                                        <SelectContent>
                                            {referenceData?.gostekhnadzor?.map((item) => (
                                                <SelectItem key={item.id} value={String(item.id)}>
                                                    {item.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                            )}
                        />

                        <Controller
                            name="judicial_procedure_initiation.procedureInitiationFns"
                            control={control}
                            render={({field}) => (
                                <div className="space-y-2">
                                    <Label htmlFor="judicial_procedure_initiation.procedureInitiationFns">ФНС</Label>
                                    <Select
                                        value={toIdString(field.value)}
                                        onValueChange={field.onChange}
                                    >
                                        <SelectTrigger id="judicial_procedure_initiation.procedureInitiationFns">
                                            <SelectValue placeholder="Выберите ФНС"/>
                                        </SelectTrigger>
                                        <SelectContent>
                                            {referenceData?.fns?.map((item) => (
                                                <SelectItem key={item.id} value={String(item.id)}>
                                                    {item.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                            )}
                        />

                        <Controller
                            name="judicial_procedure_initiation.procedureInitiationRosgvardia"
                            control={control}
                            render={({field}) => (
                                <div className="space-y-2">
                                    <Label htmlFor="judicial_procedure_initiation.procedureInitiationRosgvardia">
                                        Росгвардия
                                    </Label>
                                    <Select
                                        value={toIdString(field.value)}
                                        onValueChange={field.onChange}
                                    >
                                        <SelectTrigger id="judicial_procedure_initiation.procedureInitiationRosgvardia">
                                            <SelectValue placeholder="Выберите Росгвардию"/>
                                        </SelectTrigger>
                                        <SelectContent>
                                            {referenceData?.rosgvardia?.map((item) => (
                                                <SelectItem key={item.id} value={String(item.id)}>
                                                    {item.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                            )}
                        />

                        <div className="space-y-1">
                            <Label htmlFor="judicial_procedure_initiation.procedureInitiationJudge">Судья</Label>
                            <Input
                                id="judicial_procedure_initiation.procedureInitiationJudge"
                                placeholder="Иванов Иван Иванович"
                                {...register("judicial_procedure_initiation.procedureInitiationJudge")}
                            />
                        </div>

                        <Controller
                            name="judicial_procedure_initiation.procedureInitiationBailiff"
                            control={control}
                            render={({field}) => (
                                <div className="space-y-2">
                                    <Label htmlFor="judicial_procedure_initiation.procedureInitiationBailiff">
                                        Судебный пристав
                                    </Label>
                                    <Select
                                        value={toIdString(field.value)}
                                        onValueChange={field.onChange}
                                    >
                                        <SelectTrigger id="judicial_procedure_initiation.procedureInitiationBailiff">
                                            <SelectValue placeholder="Выберите пристава"/>
                                        </SelectTrigger>
                                        <SelectContent>
                                            {referenceData?.bailiffs?.map((item) => (
                                                <SelectItem key={item.id} value={String(item.id)}>
                                                    {item.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                            )}
                        />

                        <div className="space-y-1">
                            <Label htmlFor="judicial_procedure_initiation.procedureInitiationSpecialAccountNumber">
                                Номер спец счёта
                            </Label>
                            <Input
                                id="judicial_procedure_initiation.procedureInitiationSpecialAccountNumber"
                                placeholder="40817810099910004312"
                                {...register("judicial_procedure_initiation.procedureInitiationSpecialAccountNumber")}
                            />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="judicial_procedure_initiation.correspondenceAddress">
                                Адрес для направления корреспонденции
                            </Label>
                            <Input
                                id="judicial_procedure_initiation.correspondenceAddress"
                                {...register("judicial_procedure_initiation.correspondenceAddress")}
                                placeholder="195112, г. Санкт-Петербург, а/я 16"
                            />
                        </div>

                        <div className="col-span-full space-y-3">
                            <div className="flex items-center justify-between">
                                <Label className="font-medium text-lg">
                                    Окончания исполнительных производств
                                </Label>
                            </div>

                            <Controller
                                control={control}
                                name="judicial_procedure_initiation.procedureInitiationIPEndings"
                                render={({field: executionTerminationsField}) => {
                                    const executionTerminationsArray = executionTerminationsField.value ?? [];

                                    return (
                                        <>
                                            {executionTerminationsArray.length === 0 ? (
                                                <div className="space-y-1">
                                                    <Label>Окончание 1</Label>
                                                    <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                                                        <div className="space-y-1">
                                                            <Input
                                                                placeholder="12345/24/77001-ИП"
                                                                onChange={(e) => {
                                                                    const currentTermination = executionTerminationsArray.length > 0 ? executionTerminationsArray[0] : {
                                                                        number: "",
                                                                        date: "",
                                                                        amount: ""
                                                                    };
                                                                    executionTerminationsField.onChange([
                                                                        {...currentTermination, number: e.target.value},
                                                                    ]);
                                                                }}
                                                            />
                                                        </div>
                                                        <DatePickerInput
                                                            placeholder="Выберите дату"
                                                            onChange={(value) => {
                                                                const currentTermination = executionTerminationsArray.length > 0 ? executionTerminationsArray[0] : {
                                                                    number: "",
                                                                    date: "",
                                                                    amount: ""
                                                                };
                                                                executionTerminationsField.onChange([
                                                                    {...currentTermination, date: value},
                                                                ]);
                                                            }}
                                                        />
                                                        <div className="space-y-1">
                                                            <Input
                                                                type="number"
                                                                step="0.01"
                                                                placeholder="Сумма долга"
                                                                onChange={(e) => {
                                                                    const currentTermination = executionTerminationsArray.length > 0 ? executionTerminationsArray[0] : {
                                                                        number: "",
                                                                        date: "",
                                                                        amount: ""
                                                                    };
                                                                    executionTerminationsField.onChange([
                                                                        {...currentTermination, amount: e.target.value},
                                                                    ]);
                                                                }}
                                                            />
                                                        </div>
                                                    </div>
                                                </div>
                                            ) : (
                                                <div className="space-y-3">
                                                    {executionTerminationsArray.map(
                                                        (
                                                            terminationItem: {
                                                                number: string;
                                                                date: string;
                                                                amount?: string | null
                                                            },
                                                            terminationIndex: number
                                                        ) => (
                                                            <div
                                                                key={terminationIndex}
                                                                className="flex items-end gap-3"
                                                            >
                                                                <div className="flex-1 space-y-1">
                                                                    <Label>
                                                                        Окончание {terminationIndex + 1}
                                                                    </Label>
                                                                    <div
                                                                        className="grid grid-cols-1 gap-4 md:grid-cols-3">
                                                                        <div className="space-y-1">
                                                                            <Input
                                                                                value={terminationItem.number ?? ""}
                                                                                placeholder="12345/24/77001-ИП"
                                                                                onChange={(e) => {
                                                                                    const newTerminations = [
                                                                                        ...executionTerminationsArray,
                                                                                    ];
                                                                                    newTerminations[terminationIndex] = {
                                                                                        ...terminationItem,
                                                                                        number: e.target.value,
                                                                                    };
                                                                                    executionTerminationsField.onChange(newTerminations);
                                                                                }}
                                                                            />
                                                                        </div>
                                                                        <DatePickerInput
                                                                            value={terminationItem.date ?? ""}
                                                                            placeholder="Выберите дату"
                                                                            onChange={(value) => {
                                                                                const newTerminations = [
                                                                                    ...executionTerminationsArray,
                                                                                ];
                                                                                newTerminations[terminationIndex] = {
                                                                                    ...terminationItem,
                                                                                    date: value,
                                                                                };
                                                                                executionTerminationsField.onChange(newTerminations);
                                                                            }}
                                                                        />
                                                                        <div className="space-y-1">
                                                                            <Input
                                                                                type="number"
                                                                                step="0.01"
                                                                                value={terminationItem.amount ?? ""}
                                                                                placeholder="Сумма долга"
                                                                                onChange={(e) => {
                                                                                    const newTerminations = [
                                                                                        ...executionTerminationsArray,
                                                                                    ];
                                                                                    newTerminations[terminationIndex] = {
                                                                                        ...terminationItem,
                                                                                        amount: e.target.value,
                                                                                    };
                                                                                    executionTerminationsField.onChange(newTerminations);
                                                                                }}
                                                                            />
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <Button
                                                                    type="button"
                                                                    variant="ghost"
                                                                    size="icon"
                                                                    className="text-red-400"
                                                                    onClick={() => {
                                                                        const newTerminations = executionTerminationsArray.filter(
                                                                            (_: unknown, i: number) =>
                                                                                i !== terminationIndex
                                                                        );
                                                                        executionTerminationsField.onChange(newTerminations);
                                                                    }}
                                                                    aria-label={`Удалить окончание ${terminationIndex + 1}`}
                                                                >
                                                                    <Trash2 className="h-4 w-4"/>
                                                                </Button>
                                                            </div>
                                                        )
                                                    )}
                                                </div>
                                            )}

                                            <Button
                                                type="button"
                                                variant="outline"
                                                onClick={() => {
                                                    const currentTerminations = executionTerminationsField.value ?? [];
                                                    executionTerminationsField.onChange([
                                                        ...currentTerminations,
                                                        {number: "", date: "", amount: ""},
                                                    ]);
                                                }}
                                            >
                                                Добавить окончание
                                            </Button>
                                        </>
                                    );
                                }}
                            />
                        </div>
                    </div>

                    <DocumentsList
                        documents={documents}
                        title="Документы этапа введения:"
                        formValues={watch()}
                        onDocumentClick={openDocument}
                        onDownload={onDownload}
                        onNavigateToField={onNavigateToField}
                    />
                </CardContent>
            </Card>
        </TabsContent>
    );
};
